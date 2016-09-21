<?php

namespace app\classes\uu\tarificator;

use app\classes\Event;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
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

            $andWhereSQL = '';
            if ($accountTariffId) {
                $andWhereSQL .= " AND account_tariff.id = {$accountTariffId} ";
            }

            // По каждой таймзоне обновить текущий (по его таймзоне) тариф
            $sql = <<<SQL
            UPDATE
                {$accountTariffTableName} account_tariff,
                (
                    SELECT
                        account_tariff.client_account_id,
                        account_tariff.id AS account_tariff_id,
                        account_tariff.tariff_period_id,
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
                        ) AS new_tariff_period_id
                    FROM
                        {$clientAccountTableName} clients,
                        {$accountTariffTableName} account_tariff
                    WHERE
                        clients.id = account_tariff.client_account_id
                        AND clients.timezone_name = '{$timezoneName}'
                        {$andWhereSQL}
                    HAVING 
                        IFNULL(account_tariff.tariff_period_id, 0) != IFNULL(new_tariff_period_id, 0)
                ) a
            SET
                account_tariff.tariff_period_id = a.new_tariff_period_id,
                account_tariff.is_updated = 1
            WHERE 
                account_tariff.id = a.account_tariff_id
SQL;

            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {

                AccountTariff::updateAll(
                    ['is_updated' => 0],
                    ['is_updated' => 1]
                );

                $count = $db->createCommand($sql)
                    ->execute();
                echo $count . ' ';

                /** @var AccountTariff $accountTariff */
                foreach (AccountTariff::find()
                             ->where(['is_updated' => 1])
                             ->each() as $accountTariff) {

                    switch ($accountTariff->service_type_id) {
                        case ServiceType::ID_VOIP: {
                            Event::go(Event::UU_ACCOUNT_TARIFF_VOIP, [
                                'account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                                'number' => $accountTariff->voip_number
                            ]);
                            break;
                        }

                        case ServiceType::ID_VPBX: {
                            Event::go(Event::UU_ACCOUNT_TARIFF_VPBX, [
                                'account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                            ]);
                            break;
                        }
                    }
                }

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
