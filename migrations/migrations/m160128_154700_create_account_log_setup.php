<?php

use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\TariffPeriod;

class m160128_154700_create_account_log_setup extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = AccountLogSetup::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // date
            'date' => $this->date()->notNull(),

            // fk
            'tariff_period_id' => $this->integer()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
//            'transaction_id' => $this->integer(),

            // float
            'price' => $this->float()->notNull(),

            'insert_time' => $this->timestamp()->notNull(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $fieldName = 'tariff_period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffPeriod::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'account_tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountTariff::tableName(),
            'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable(AccountLogSetup::tableName());
    }
}
