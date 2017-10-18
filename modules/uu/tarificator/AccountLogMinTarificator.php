<?php

namespace app\modules\uu\tarificator;

use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\TariffPeriod;
use Yii;

/**
 * Предварительное списание (транзакции) минимальной платы за ресурсы. Тарификация
 */
class AccountLogMinTarificator extends Tarificator
{
    /**
     * Предварительное списание (транзакции) минимальной платы за ресурсы
     * Если указана услуга - только для нее, иначе для всех
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @throws \yii\db\Exception
     */
    public function tarificate($accountTariffId = null)
    {
        $db = Yii::$app->db;
        $accountLogMinTableName = AccountLogMin::tableName();

        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $accountLogResourceTableName = AccountLogResource::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        // удалить всё
        $this->out('. ');
        if ($accountTariffId) {
            $truncateSQL = "DELETE FROM {$accountLogMinTableName} WHERE account_tariff_id = {$accountTariffId}";
        } else {
            $truncateSQL = "TRUNCATE TABLE {$accountLogMinTableName}";
        }

        $db->createCommand($truncateSQL)
            ->execute();
        unset($truncateSQL);

        // создать заново
        $this->out('. ');
        $insertSql = <<<SQL
            INSERT INTO {$accountLogMinTableName} (
                id,	
                date_from,
                date_to,
                tariff_period_id,
                account_tariff_id,
                account_entry_id,
                period_price,
                coefficient,
                price_with_coefficient,
                price_resource,
                price
            )
            SELECT
                account_log_period.id,	
                account_log_period.date_from,
                account_log_period.date_to,
                account_log_period.tariff_period_id,
                account_log_period.account_tariff_id,
                null as account_entry_id,
                tariff_period.price_min as period_price,
                account_log_period.coefficient,
                tariff_period.price_min * account_log_period.coefficient as price_with_coefficient,
                0 as price_resource,
                0 as price
            FROM
               {$accountLogPeriodTableName} account_log_period,
               {$tariffPeriodTableName} tariff_period
            WHERE
                account_log_period.tariff_period_id = tariff_period.id
SQL;
        if ($accountTariffId) {
            $insertSql .= " AND account_log_period.account_tariff_id = {$accountTariffId}";
        }

        $db->createCommand($insertSql)
            ->execute();
        unset($insertSql);

        // обновить стоимость ресурсов
        $this->out('. ');
        $updateSqlWhereTmp = $accountTariffId ? " WHERE account_log_resource.account_tariff_id = {$accountTariffId}" : '';
        $updateSql = <<<SQL
            UPDATE 
                {$accountLogMinTableName} account_log_min,
                (
                    SELECT
                        account_log_resource.account_tariff_id,
                        DATE_FORMAT(account_log_resource.date_from, "%Y-%m-01") as date,
                        SUM(GREATEST(0, account_log_resource.price)) as price 
                    FROM {$accountLogResourceTableName} account_log_resource
                    {$updateSqlWhereTmp}
                    GROUP BY
                        account_log_resource.account_tariff_id,
                        DATE_FORMAT(account_log_resource.date_from, "%Y-%m-01") 
                ) account_log_resource_groupped
            SET
                account_log_min.price_resource = account_log_resource_groupped.price
            WHERE
                 account_log_min.account_tariff_id = account_log_resource_groupped.account_tariff_id
                 AND DATE_FORMAT(account_log_min.date_from, "%Y-%m-01") = account_log_resource_groupped.date
SQL;
        if ($accountTariffId) {
            $updateSql .= " AND account_log_min.account_tariff_id = {$accountTariffId}";
        }

        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        // обновить итоговую стоимость
        $this->out('. ');
        $updateSql = <<<SQL
            UPDATE {$accountLogMinTableName}
            SET price = GREATEST(0, price_with_coefficient - price_resource)
SQL;
        if ($accountTariffId) {
            $updateSql .= " WHERE account_tariff_id = {$accountTariffId}";
        }

        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }
}
