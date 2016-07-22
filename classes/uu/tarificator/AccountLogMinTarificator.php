<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\TariffPeriod;
use Yii;

/**
 * Предварительное списание (транзакции) минимальной платы за ресурсы. Тарификация
 */
class AccountLogMinTarificator
{
    /**
     * Предварительное списание (транзакции) минимальной платы за ресурсы
     */
    public function tarificateAll()
    {
        $db = Yii::$app->db;
        $accountLogMinTableName = AccountLogMin::tableName();

        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $accountLogResourceTableName = AccountLogResource::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        // удалить всё
        echo '. ';
        $truncateSQL = "TRUNCATE TABLE {$accountLogMinTableName}";
        $db->createCommand($truncateSQL)
            ->execute();
        unset($truncateSQL);

        // создать заново
        echo '. ';
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
        $db->createCommand($insertSql)
            ->execute();
        unset($insertSql);

        // обновить стоимость ресурсов
        echo '. ';
        $updateSql = <<<SQL
            UPDATE 
                {$accountLogMinTableName} account_log_min,
                (
                    SELECT
                        DATE_FORMAT(account_log_resource.date, "%Y-%m-01") as date,
                        SUM(account_log_resource.price) as price 
                    FROM {$accountLogResourceTableName} account_log_resource
                    GROUP BY DATE_FORMAT(account_log_resource.date, "%Y-%m-01") 
                ) account_log_resource_groupped
            SET
                account_log_min.price_resource = account_log_resource_groupped.price
            WHERE
                 account_log_min.date_from = account_log_resource_groupped.date
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        // обновить итоговую стоимость
        echo '. ';
        $updateSql = <<<SQL
            UPDATE {$accountLogMinTableName}
            SET price = GREATEST(0, price_with_coefficient - price_resource)
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }
}
