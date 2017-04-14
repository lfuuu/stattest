<?php

namespace app\modules\uu\tarificator;

use app\models\ClientAccount;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Bill;
use Yii;

/**
 * Расчет для счетов (Bill)
 */
class BillTarificator extends Tarificator
{
    /**
     * На основе новых проводок создать новые счета или добавить в существующие
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    public function tarificate($accountTariffId = null)
    {
        $db = Yii::$app->db;
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $accountEntryTypeIdPeriod = AccountEntry::TYPE_ID_PERIOD;
        $clientAccountTableName = ClientAccount::tableName();

        if ($accountTariffId) {
            $sqlAndWhere = ' AND account_entry.account_tariff_id = ' . $accountTariffId;
        } else {
            $sqlAndWhere = '';
        }

        // создать пустые счета
        $this->out('. ');
        $insertSQL = <<<SQL
            INSERT INTO {$billTableName}
            (date, client_account_id, price, is_default)
                SELECT DISTINCT
                    IF((account_entry.is_default = 1 AND account_entry.type_id != {$accountEntryTypeIdPeriod}) OR client_account.is_postpaid = 1, account_entry.date + INTERVAL 1 MONTH, account_entry.date) AS date,
                    account_tariff.client_account_id,
                    0,
                    account_entry.is_default
                FROM
                    {$accountEntryTableName} account_entry,
                    {$accountTariffTableName} account_tariff,
                    {$clientAccountTableName} client_account
                WHERE
                    account_entry.bill_id IS NULL
                    AND account_entry.account_tariff_id = account_tariff.id
                    AND account_tariff.client_account_id = client_account.id
                    {$sqlAndWhere}
            ON DUPLICATE KEY UPDATE price = 0
SQL;
        $db->createCommand($insertSQL)
            ->execute();

        // привязать проводки к счетам
        // абонентку за будущий месяц, все остальное - за прошлый
        $this->out('. ');
        $updateSql = <<<SQL
            UPDATE
               {$billTableName} bill,
               {$accountEntryTableName} account_entry,
               {$accountTariffTableName} account_tariff,
               {$clientAccountTableName} client_account
            SET
               account_entry.bill_id = bill.id
            WHERE
               account_entry.account_tariff_id = account_tariff.id
               AND account_entry.bill_id IS NULL
               AND IF((account_entry.is_default = 1 AND account_entry.type_id != {$accountEntryTypeIdPeriod}) OR client_account.is_postpaid = 1, account_entry.date + INTERVAL 1 MONTH, account_entry.date) = bill.date
               AND account_entry.is_default = bill.is_default
               AND account_tariff.client_account_id = bill.client_account_id
               AND account_tariff.client_account_id = client_account.id
               {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        // пересчитать стоимость счетов
        $this->out('. ');
        $updateSql = <<<SQL
            UPDATE
                {$billTableName} bill
            LEFT JOIN
                (
                    SELECT
                       bill_id,
                       SUM(price) AS price
                    FROM
                       {$accountEntryTableName} account_entry
                    GROUP BY
                       bill_id
                ) t
            ON bill.id = t.bill_id
            SET
                bill.price = COALESCE(t.price, 0),
                bill.is_converted = 0
            WHERE
                t.price IS NULL
                OR bill.price != t.price
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);
    }
}
