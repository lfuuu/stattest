<?php

class m150828_092736_user_users_usergroup_key extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `user_users`
                CHANGE COLUMN `usergroup` `usergroup` VARCHAR(50) NULL DEFAULT 'client' COLLATE 'utf8_bin' AFTER `pass`,
                ADD CONSTRAINT `fk_user_users__user_group` FOREIGN KEY (`usergroup`) REFERENCES `user_groups` (`usergroup`) ON UPDATE CASCADE ON DELETE SET NULL;
        ");
    }

    public function down()
    {
        echo "m150828_092736_user_users_usergroup_key cannot be reverted.\n";

        return false;
    }
}