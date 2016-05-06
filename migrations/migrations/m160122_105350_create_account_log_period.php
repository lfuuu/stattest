<?php

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\TariffPeriod;

class m160122_105350_create_account_log_period extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = AccountLogPeriod::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // date
            'date_from' => $this->date()->notNull(),
            'date_to' => $this->date()->notNull(),

            // fk
            'tariff_period_id' => $this->integer()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
//            'transaction_id' => $this->integer(),

            // float
            'period_price' => $this->float()->notNull(),
            'coefficient' => $this->float()->notNull(),
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
        $this->dropTable(AccountLogPeriod::tableName());
    }
}
