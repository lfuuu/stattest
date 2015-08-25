<?php

class m150824_150630_user_city extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `user_users`
                ADD COLUMN `city_id` INT(10) NULL DEFAULT NULL AFTER `language`,
                ADD CONSTRAINT `fk_user_users__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE;
        ");
    }

    public function down()
    {
        echo "m150824_150630_user_city cannot be reverted.\n";

        return false;
    }
}