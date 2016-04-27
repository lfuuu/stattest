<?php

use app\classes\uu\model\Bill;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\models\ClientAccount;

class m160427_115700_create_bill extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->createBill();
        $this->addAccountEntryBill();
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->dropAccountEntryBill();
        $this->dropTable(Bill::tableName());
    }

    /**
     * создать Bill
     */
    private function createBill()
    {
        $tableName = Bill::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'client_account_id' => $this->integer()->notNull(),
            'price' => $this->float()->notNull(),
            'update_time' => $this->timestamp()->notNull(),
        ]);

        $fieldName = 'client_account_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ClientAccount::tableName(), 'id', 'RESTRICT');

        $this->createIndex('uniq-' . $tableName . '-' . 'date-client_account_id', $tableName, ['date', 'client_account_id'], true);

    }

    /**
     * добавить поле AccountEntry.Bill
     */
    public function addAccountEntryBill()
    {
        $tableName = AccountEntry::tableName();
        $fieldName = 'bill_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Bill::tableName(), 'id', 'RESTRICT');
    }

    /**
     * удалить поле AccountEntry.Bill
     */
    public function dropAccountEntryBill()
    {
        $tableName = AccountEntry::tableName();
        $fieldName = 'bill_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);
    }

}