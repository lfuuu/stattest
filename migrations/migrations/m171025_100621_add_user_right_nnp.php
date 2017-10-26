<?php

/**
 * Class m171025_100621_add_user_right_nnp
 */
class m171025_100621_add_user_right_nnp extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addUserRights('nnp', 'ННП');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropUserRights('nnp');
    }
}
