<?php

use app\models\ClientAccount;

/**
 * Class m180207_182141_client_show_in_lk
 */
class m180207_182141_client_show_in_lk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientAccount::tableName(), 'show_in_lk',
            $this->integer()
                ->notNull()
                ->defaultValue(ClientAccount::SHOW_IN_LK_ALWAYS)
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientAccount::tableName(), 'show_in_lk');
    }
}
