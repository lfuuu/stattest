<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;

/**
 * Handles the creation for table `account_tariff_resource_log`.
 */
class m170301_113847_create_account_tariff_resource_log extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableName = AccountTariffResourceLog::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'account_tariff_id' => $this->integer()->notNull(),
            'resource_id' => $this->integer()->notNull(),
            'amount' => $this->float()->notNull(),
            'actual_from_utc' => $this->dateTime()->notNull(),

            'insert_time' => $this->timestamp()->notNull(), // dateTime
            'insert_user_id' => $this->integer(),
        ]);

        $fieldName = 'insert_user_id';
        $this->addForeignKey('fk-' . $fieldName, $tableName, $fieldName, \app\models\User::tableName(), 'id', 'SET NULL');

        $fieldName = 'account_tariff_id';
        $this->addForeignKey('fk-' . $fieldName, $tableName, $fieldName, AccountTariff::tableName(), 'id', 'RESTRICT');

        $fieldName = 'resource_id';
        $this->addForeignKey('fk-' . $fieldName, $tableName, $fieldName, Resource::tableName(), 'id', 'RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $tableName = AccountTariffResourceLog::tableName();
        $this->dropTable($tableName);
    }
}
