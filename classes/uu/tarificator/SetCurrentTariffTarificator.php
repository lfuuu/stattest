<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\models\ClientAccount;
use DateTimeZone;
use Yii;

/**
 * Обновить AccountTariff.TariffPeriod на основе AccountTariffLog
 */
class SetCurrentTariffTarificator implements TarificatorI
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        // перебирать все услуги слишком долго. Быстрее по логу тарифов найти нужное
        // Надо учесть таймзону клиента
        $db = Yii::$app->db;
        $clientAccountTableName = ClientAccount::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        // выбрать все уникальные таймзоны
        $sql = <<<SQL
            SELECT DISTINCT timezone_name
            FROM {$clientAccountTableName}
SQL;
        $timezoneQuery = $db->createCommand($sql)
            ->query();

        foreach ($timezoneQuery as $timezone) {
            echo '# ';
            $timezoneName = $timezone['timezone_name'];
            $timezone = new DateTimeZone($timezoneName);
            $dateTime = (new \DateTimeImmutable())
                ->setTimezone($timezone);
            $clientDate = $dateTime->format('Y-m-d');

            // По каждой таймзоне обновить текущий (по его таймзоне) тариф
            $sql = <<<SQL
                UPDATE
                    {$clientAccountTableName} clients,
                    {$accountTariffTableName} account_tariff
                SET
                    account_tariff.tariff_period_id = 
                        (
                            SELECT
                                account_tariff_log.tariff_period_id
                            FROM
                                {$accountTariffLogTableName} account_tariff_log
                            WHERE
                                account_tariff.id = account_tariff_log.account_tariff_id
                                AND account_tariff_log.actual_from <= '{$clientDate}'
                            ORDER BY
                                account_tariff_log.actual_from DESC,
                                account_tariff_log.id DESC
                            LIMIT 1
                        )
                WHERE
                    clients.timezone_name = '{$timezoneName}'
                    AND clients.id = account_tariff.client_account_id
SQL;
            if ($accountTariffId) {
                $sql .= " AND account_tariff.id = {$accountTariffId} ";
            }
            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {

                $count = $db->createCommand($sql)
                    ->execute();
                echo $count . ' ';

                $isWithTransaction && $transaction->commit();
            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
                Yii::error($e->getMessage());
            }
        }
    }
}
