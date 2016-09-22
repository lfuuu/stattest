<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use Yii;

/**
 * Автоматически закрыть услугу по истечению тестового периода
 * Лучше вызывать по крону. Триггером запускать не надо, иначе нельзя будет отменить закрытие и указать другой тариф вручную
 */
class AutoCloseAccountTariffTarificator implements TarificatorI
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $db = Yii::$app->db;
        $versionBillerUniversal = ClientAccount::VERSION_BILLER_UNIVERSAL;

        $clientAccountTableName = ClientAccount::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        $sql = <<<SQL
                SELECT
                    account_tariff.id
                FROM
                    {$clientAccountTableName} clients,
                    {$accountTariffTableName} account_tariff,
                    {$tariffPeriodTableName} tariff_period,
                    {$tariffTableName} tariff
                WHERE
                    clients.account_version = {$versionBillerUniversal}
                    AND clients.id = account_tariff.client_account_id
                    AND account_tariff.tariff_period_id = tariff_period.id
                    AND tariff_period.tariff_id = tariff.id
                    AND tariff.is_autoprolongation = 0
SQL;
        if ($accountTariffId) {
            $sql .= " AND account_tariff.id = {$accountTariffId} ";
        }

        $query = $db->createCommand($sql)
            ->query();

        foreach ($query as $row) {

            echo '. ';
            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {

                $accountTariff = AccountTariff::findOne(['id' => $row['id']]);
                $accountLogHugePeriods = $accountTariff->getAccountLogHugeFromToTariffs($isWithFuture = true);
                $accountLogHugePeriod = end($accountLogHugePeriods); // последний

                $tariffPeriod = $accountLogHugePeriod->tariffPeriod;
                if (!$tariffPeriod) {
                    // уже закрыт. Вообще то сюда не должны попасть, потому что выше есть проверка
                    continue;
                }

                $tariff = $tariffPeriod->tariff;
                if ($tariff->is_autoprolongation) {
                    // уже сменили на нетестовый. Вообще то сюда не должны попасть, потому что выше есть проверка
                    continue;
                }

                if ($accountLogHugePeriod->dateTo) {
                    // тестовый тариф уже запланировали закрыть
                    continue;
                }

                $chargePeriod = $tariffPeriod->chargePeriod;
                $dateFrom = $accountLogHugePeriod->dateFrom;
                for ($i = 0; $i <= $tariff->count_of_validity_period; $i++) {
                    $dateTo = $chargePeriod->monthscount ? $dateFrom->modify('last day of this month') : $dateFrom;
                    // начать новый период
                    $dateFrom = $dateTo->modify('+1 day');
                }

                // через модель не надо, иначе сработают триггеры и пересчет запустится рекурсивно.
                // поскольку запуск по крону, то он и так все сразу пересчитает
                $sql = <<<SQL
                    INSERT INTO {$accountTariffLogTableName}
                        (account_tariff_id, tariff_period_id, actual_from, insert_time)
                    VALUES
                        (:account_tariff_id, :tariff_period_id, :actual_from, :insert_time)
SQL;
                $db->createCommand($sql, [
                    ':account_tariff_id' => $accountTariff->id,
                    ':tariff_period_id' => null,
                    ':actual_from' => $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT),
                    ':insert_time' => $dateFrom->modify('-1 day')->format(DateTimeZoneHelper::DATE_FORMAT . ' 20:59:58'), // 58 секунд специально, чтобы не путать со старым скриптом, в котором 59
                ])
                    ->execute();

                $isWithTransaction && $transaction->commit();
            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
                Yii::error($e->getMessage());
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }
}
