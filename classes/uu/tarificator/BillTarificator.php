<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Bill;
use Yii;

/**
 * Расчет для счетов (Bill)
 */
class BillTarificator
{
    /**
     * На основе новых проводок создать новые счета или добавить в существующие
     */
    public function tarificateAll()
    {
        $db = Yii::$app->db;
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        // создать пустые счета
        echo '. ';
        $insertSQL = <<<SQL
            INSERT INTO {$billTableName}
            (date, client_account_id, price)
                SELECT DISTINCT
                    account_entry.date,
                    account_tariff.client_account_id,
                    0
                FROM
                    {$accountEntryTableName} account_entry,
                    {$accountTariffTableName} account_tariff
                WHERE
                    account_entry.bill_id IS NULL
                    AND account_entry.account_tariff_id = account_tariff.id
            ON DUPLICATE KEY UPDATE price = 0
SQL;
        $db->createCommand($insertSQL)
            ->execute();

        // привязать проводки к счетам
        echo '. ';
        $updateSql = <<<SQL
            UPDATE
               {$billTableName} bill,
               {$accountEntryTableName} account_entry,
               {$accountTariffTableName} account_tariff
            SET
               account_entry.bill_id = bill.id
            WHERE
               account_entry.account_tariff_id = account_tariff.id
               AND account_entry.bill_id IS NULL
               AND account_entry.date = bill.date
               AND account_tariff.client_account_id = bill.client_account_id
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        // пересчитать стоимость счетов
        echo '. ';
        $updateSql = <<<SQL
            UPDATE
            {$billTableName} bill,
            (
                SELECT
                   bill_id,
                   SUM(price) AS price
                FROM
                   {$accountEntryTableName}
                GROUP BY
                   bill_id
            ) t
         SET
            bill.price = t.price
         WHERE
            bill.id = t.bill_id
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        echo PHP_EOL;
    }
}
