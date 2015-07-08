<?php

class m150707_133944_remove_firms_from_rights extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            DELETE FROM `user_grant_users` WHERE `resource`='firms';
        ");
    }

    public function down()
    {
        echo "m150707_133944_remove_firms_from_rights cannot be reverted.\n";

        return false;
    }
}