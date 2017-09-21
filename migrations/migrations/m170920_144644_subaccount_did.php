<?php
use app\models\ClientSubAccount;

/**
 * Class m170920_144644_subaccount_did
 */
class m170920_144644_subaccount_did extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientSubAccount::tableName(), 'did', $this->string(16));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientSubAccount::tableName(), 'did');
    }
}
