<?php

use app\models\ClientAccount;

/**
 * Class m180312_085451_drop_clients_timezone_offset
 */
class m180312_085451_drop_clients_timezone_offset extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->dropColumn(ClientAccount::tableName(), 'timezone_offset');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->addColumn(ClientAccount::tableName(), 'timezone_offset', $this->integer(4)->notNull()->defaultValue(3));
    }
}
