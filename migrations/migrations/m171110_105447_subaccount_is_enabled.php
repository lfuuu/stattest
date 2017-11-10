<?php

use app\models\ClientSubAccount;

/**
 * Class m171110_105447_subaccount_is_enabled
 */
class m171110_105447_subaccount_is_enabled extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientSubAccount::tableName(), 'is_enabled', $this->boolean()->defaultValue(true));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientSubAccount::tableName(), 'is_enabled');
    }
}
