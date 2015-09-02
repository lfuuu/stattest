<?php

class m150828_123829_user_users_department_key extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `user_users`
                CHANGE COLUMN `depart_id` `depart_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `data_flags`;
        ");
        $this->execute("
            UPDATE `user_users` SET `depart_id` = NULL WHERE `depart_id` = 0;
        ");
        $this->execute("
            ALTER TABLE `user_users`
                ADD CONSTRAINT `fk_user_users__user_department` FOREIGN KEY (`depart_id`) REFERENCES `user_departs` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;
        ");
    }

    public function down()
    {
        echo "m150828_123829_user_users_department_key cannot be reverted.\n";

        return false;
    }
}