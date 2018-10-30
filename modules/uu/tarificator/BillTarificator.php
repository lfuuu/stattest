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
     * @throws \yii\db\Exception
     */
    public function tarificate($accountTariffId = null)
    {
        $db = Yii::$app->db;
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();

        $sqlAndWhere = '';

        if ($accountTariffId) {
            $sqlAndWhere = ' AND account_entry.account_tariff_id = ' . $accountTariffId;
        }

        // создать пустые счета
        $this->out('. ');
        $insertSQL = <<<SQL
            INSERT INTO {$billTableName}
            (date, client_account_id, price)
                SELECT DISTINCT
                    IF(account_entry.is_next_month = 1, 
                        DATE_FORMAT(account_entry.date, '%Y-%m-01') + INTERVAL 1 MONTH, 
                        DATE_FORMAT(account_entry.date, '%Y-%m-01')
                    ) AS date,
                    account_tariff.client_account_id,
                    0
                FROM
                    {$accountEntryTableName} account_entry,
                    {$accountTariffTableName} account_tariff,
                    {$clientAccountTableName} client_account
                WHERE
                    account_entry.bill_id IS NULL
                    AND account_entry.account_tariff_id = account_tariff.id
                    AND account_tariff.client_account_id = client_account.id
                    {$sqlAndWhere}
                ORDER BY
                    account_entry.is_next_month ASC
            ON DUPLICATE KEY UPDATE price = 0
SQL;
        $db->createCommand($insertSQL)
            ->execute();

        // привязать проводки к счетам
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
                AND IF(account_entry.is_next_month = 1, 
                    DATE_FORMAT(account_entry.date, '%Y-%m-01') + INTERVAL 1 MONTH, 
                    DATE_FORMAT(account_entry.date, '%Y-%m-01')
                ) = bill.date
                AND account_tariff.client_account_id = bill.client_account_id
                AND account_tariff.client_account_id = client_account.id
                {$sqlAndWhere}
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);


        // при пересчете услуги - обновление счетов в рамках ЛС
        $whereAccountEntry = $whereBill = "";
        if ($accountTariffId) {
            $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
            if ($accountTariff) {
                $whereAccountEntry = ', ' . AccountTariff::tableName() . ' as account_tariff 
                WHERE 
                    account_tariff.id=account_entry.account_tariff_id 
                    AND account_tariff.client_account_id = ' . $accountTariff->client_account_id;

                $whereBill = 'AND bill.client_account_id = ' . $accountTariff->client_account_id;
            }
        }

        $queryAccountEntry = <<<SQL
                    SELECT
                      account_entry.bill_id,
                      SUM(account_entry.price_with_vat) AS price
                    FROM
                      {$accountEntryTableName} account_entry
                      {$whereAccountEntry}
                    GROUP BY
                      bill_id
SQL;

        $tmpTableName = 'uu_entry_tmp';
        $tmpCreateTable = "CREATE TEMPORARY TABLE {$tmpTableName} (INDEX(bill_id)) " . $queryAccountEntry;
        $db->createCommand($tmpCreateTable)->execute();


        // пересчитать стоимость счетов
        $this->out('. ');
        $updateSql = <<<SQL
            UPDATE
                {$billTableName} bill
            INNER JOIN {$tmpTableName} t
            ON bill.id = t.bill_id
            SET
                bill.price = ROUND(COALESCE(t.price, 0), 4),
                bill.is_converted = 0
            WHERE
                (t.price IS NULL OR bill.price != ROUND(t.price, 4))
                {$whereBill}
SQL;
        $db->createCommand($updateSql)
            ->execute();
        unset($updateSql);

        $db->createCommand("DROP TEMPORARY TABLE IF EXISTS {$tmpTableName}")->execute();
    }
}
