<?php

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\TariffPeriod;

class m160721_162035_uu_account_log_min extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = AccountLogMin::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // date
            'date_from' => $this->date()->notNull(),
            'date_to' => $this->date()->notNull(),

            // fk
            'tariff_period_id' => $this->integer()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
            'account_entry_id' => $this->integer(),

            // float
            'period_price' => $this->float()->notNull(),
            'coefficient' => $this->float()->notNull(),
            'price_with_coefficient' => $this->float()->notNull(),
            'price_resource' => $this->float()->notNull(),
            'price' => $this->float()->notNull(),

            'insert_time' => $this->timestamp()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'tariff_period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffPeriod::tableName(), 'id', 'RESTRICT');

        $fieldName = 'account_tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountTariff::tableName(), 'id', 'CASCADE');

        $fieldName = 'account_entry_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountEntry::tableName(), 'id', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable(AccountLogMin::tableName());
    }
}