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
        $clientAccountId = null;
        if ($accountTariffId) {
            $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
            $clientAccountId = $accountTariff ? $accountTariff->client_account_id : null;
            unset($accountTariff);
        }

        // --------------------------------------------
        // Создать пустые счета, если их еще нет
        // --------------------------------------------
        $this->out('. ');
        $this->createEmptyBills($accountTariffId);

        // --------------------------------------------
        // Привязать проводки к счетам
        // --------------------------------------------
        $this->out('. ');
        $this->linkEntryToBill($accountTariffId);

        // --------------------------------------------
        // Подготовить данные для обновления счетов
        // --------------------------------------------
        $this->out('. ');
        $this->createTmpTable($clientAccountId, $tmpTableName = 'uu_entry_tmp');

        // --------------------------------------------
        // Обновить стоимость счетов
        // --------------------------------------------
        $this->out('. ');
        $this->updateBill($clientAccountId, $tmpTableName);

        // --------------------------------------------
        // Удалить временную таблицу
        // --------------------------------------------
        $this->out('. ');
        $this->dropTmpTable($tmpTableName);
    }

    /**
     * Создать пустые счета, если их еще нет
     *
     * @param int $accountTariffId
     * @throws \yii\db\Exception
     */
    protected function createEmptyBills($accountTariffId)
    {
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();

        $sqlWhere = $accountTariffId ?
            ' AND account_entry.account_tariff_id = ' . $accountTariffId :
            '';

        $sqlInsert = <<<SQL
            INSERT INTO {$billTableName}
            (operation_type_id, date, client_account_id, price)
                SELECT DISTINCT
                    account_entry.operation_type_id,
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
                    {$sqlWhere}
                ORDER BY
                    account_entry.is_next_month ASC
            ON DUPLICATE KEY UPDATE price = 0
SQL;
        Yii::$app->db
            ->createCommand($sqlInsert)
            ->execute();
    }

    /**
     * Привязать проводки к счетам
     *
     * @param int $accountTariffId
     * @throws \yii\db\Exception
     */
    protected function linkEntryToBill($accountTariffId)
    {
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();

        $sqlWhere = $accountTariffId ?
            ' AND account_entry.account_tariff_id = ' . $accountTariffId :
            '';

        $sqlUpdate = <<<SQL
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
                AND account_entry.operation_type_id = bill.operation_type_id
                {$sqlWhere}
SQL;
        Yii::$app->db
            ->createCommand($sqlUpdate)
            ->execute();
    }

    /**
     * Подготовить данные для обновления счетов
     *
     * @param int $clientAccountId
     * @param string $tmpTableName
     * @throws \yii\db\Exception
     */
    protected function createTmpTable($clientAccountId, $tmpTableName)
    {
        $accountEntryTableName = AccountEntry::tableName();

        $sqlJoin = $clientAccountId ?
            ' INNER JOIN ' . AccountTariff::tableName() . ' as account_tariff 
              ON account_tariff.id = account_entry.account_tariff_id 
                 AND account_tariff.client_account_id = ' . $clientAccountId :
            '';

        $sqlSelect = <<<SQL
                    SELECT
                      account_entry.bill_id,
                      SUM(account_entry.price_with_vat) AS price
                    FROM
                      {$accountEntryTableName} account_entry
                      {$sqlJoin}
                    GROUP BY
                      account_entry.bill_id
SQL;

        $sqlCreate = "CREATE TEMPORARY TABLE {$tmpTableName} (INDEX(bill_id)) " . $sqlSelect;
        Yii::$app->db
            ->createCommand($sqlCreate)
            ->execute();
    }

    /**
     * Обновить стоимость счетов
     *
     * @param int $clientAccountId
     * @param string $tmpTableName
     * @throws \yii\db\Exception
     */
    protected function updateBill($clientAccountId, $tmpTableName)
    {
        $billTableName = Bill::tableName();

        $sqlUpdate = <<<SQL
            UPDATE
                {$billTableName} bill
            INNER JOIN {$tmpTableName} t
            ON bill.id = t.bill_id
            SET
                bill.price = ROUND(COALESCE(t.price, 0), 4),
                bill.is_converted = 0
            WHERE
                (t.price IS NULL OR bill.price != ROUND(t.price, 4))
SQL;
        if ($clientAccountId) {
            $sqlUpdate .= 'AND bill.client_account_id = ' . $clientAccountId;
        }
        Yii::$app->db
            ->createCommand($sqlUpdate)
            ->execute();
    }

    /**
     * Удалить временную таблицу
     *
     * @param string $tmpTableName
     * @throws \yii\db\Exception
     */
    protected function dropTmpTable($tmpTableName)
    {
        Yii::$app->db
            ->createCommand("DROP TEMPORARY TABLE IF EXISTS {$tmpTableName}")
            ->execute();
    }
}
