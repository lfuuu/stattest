<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPeriod;
use app\models\ClientAccount;
use app\models\Region;
use app\models\User;

class m160119_104547_create_vbpx_account_tariff extends \app\classes\Migration
{
    public function safeUp()
    {
        $this->createAccountTariff();
        $this->createAccountTariffLog();
    }

    public function safeDown()
    {
        $this->dropTable(AccountTariffLog::tableName());
        $this->dropTable(AccountTariff::tableName());
    }

    /**
     * Создать таблицу AccountTariff
     */
    private function createAccountTariff()
    {
        $tableName = AccountTariff::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // text
            'comment' => $this->text()->notNull()->defaultValue(''),

            // fk
            'client_account_id' => $this->integer()->notNull(),
            'service_type_id' => $this->integer()->notNull(),
            'region_id' => $this->integer(),
            'prev_account_tariff_id' => $this->integer(),
            'tariff_period_id' => $this->integer(),

            'insert_time' => $this->timestamp()->notNull(), // dateTime
            'insert_user_id' => $this->integer(),
            'update_time' => $this->dateTime(),
            'update_user_id' => $this->integer(),
        ]);

        $fieldName = 'client_account_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ClientAccount::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'service_type_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, ServiceType::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'region_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Region::tableName(), 'id',
            'RESTRICT');

        $fieldName = 'prev_account_tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, $tableName, 'id',
            'RESTRICT');

        $fieldName = 'tariff_period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffPeriod::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'insert_user_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id',
            'SET NULL');

        $fieldName = 'update_user_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id',
            'SET NULL');
    }

    /**
     * Создать таблицу AccountTariffLog
     */
    private function createAccountTariffLog()
    {
        $tableName = AccountTariffLog::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),

            // date
            'actual_from' => $this->date()->notNull(),

            // fk
            'account_tariff_id' => $this->integer()->notNull(),
            'tariff_period_id' => $this->integer(),

            'insert_time' => $this->timestamp()->notNull(), // dateTime
            'insert_user_id' => $this->integer(),
        ]);

        $fieldName = 'account_tariff_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, AccountTariff::tableName(),
            'id', 'CASCADE');

        $fieldName = 'tariff_period_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, TariffPeriod::tableName(),
            'id', 'RESTRICT');

        $fieldName = 'insert_user_id';
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id',
            'SET NULL');

        $this->createIndex('i-' . $tableName . '-actual_from', $tableName, 'actual_from');
    }

}
