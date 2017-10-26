<?php

/**
 * Class m171025_092940_add_user_right
 */
class m171025_092940_add_user_right_sim extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addUserRights('sim', 'SIM-карты');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropUserRights('sim');
    }
}
