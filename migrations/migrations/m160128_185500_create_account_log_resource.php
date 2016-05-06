<?php

use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;

class m160128_185500_create_account_log_resource extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = AccountLogResource::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // date
            'date' => $this->date()->notNull(),

            // fk
            'tariff_period_id' => $this->integer()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
            'tariff_resource_id' => $this->integer()->notNull(),
//            'transaction_id' => $this->integer(),

            // float
            'amount_use' => $this->float(),
            'amount_free' => $this->float()->notNull(),
            'amount_overhead' => $this->integer(),
            'price_per_unit' => $this->float()->notNull(),
            'price' => $this->float(),

            'insert_time' => $this->timestamp()->notNull(),
        ]);

        $fieldName = 'tariff_period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffPeriod::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'account_tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountTariff::tableName(),
            'id', 'CASCADE');

        $fieldName = 'tariff_resource_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffResource::tableName(),
            'id', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable(AccountLogResource::tableName());
    }
}
