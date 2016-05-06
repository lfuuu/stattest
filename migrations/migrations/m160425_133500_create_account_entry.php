<?php

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;

class m160425_133500_create_account_entry extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->createAccountEntry();
        $this->addAccountLogEntry(AccountLogSetup::tableName());
        $this->addAccountLogEntry(AccountLogPeriod::tableName());
        $this->addAccountLogEntry(AccountLogResource::tableName());
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->dropAccountLogEntry(AccountLogSetup::tableName());
        $this->dropAccountLogEntry(AccountLogPeriod::tableName());
        $this->dropAccountLogEntry(AccountLogResource::tableName());
        $this->dropTable(AccountEntry::tableName());
    }

    /**
     * создать AccountEntry
     */
    private function createAccountEntry()
    {
        $tableName = AccountEntry::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
            'type_id' => $this->integer()->notNull(),
            'price' => $this->float()->notNull(),
            'update_time' => $this->timestamp()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'account_tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountTariff::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'type_id';
        $this->createIndex('idx-' . $tableName . '-' . $fieldName, $tableName, $fieldName);

        $this->createIndex('uniq-' . $tableName . '-' . 'date-type_id-account_tariff_id', $tableName,
            ['date', 'type_id', 'account_tariff_id'], true);

    }

    /**
     * добавить поле AccountLog*.Entry
     */
    public function addAccountLogEntry($tableName)
    {
        $fieldName = 'account_entry_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountEntry::tableName(),
            'id', 'RESTRICT');
    }

    /**
     * удалить поле AccountLog*.Entry
     */
    public function dropAccountLogEntry($tableName)
    {
        $fieldName = 'account_entry_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);
    }

}