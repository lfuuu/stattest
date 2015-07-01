<?php

class m150629_195019_tags extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `tag` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(100) NOT NULL,
                `group_id` INT NULL,
                PRIMARY KEY (`id`),
                INDEX `group_id` (`group_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ;


            CREATE TABLE `tag_group` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                `description` TEXT NOT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ;


            CREATE TABLE `tag_to_model` (
                `tag_id` INT(11) NOT NULL,
                `model_id` INT(11) NOT NULL,
                `model` VARCHAR(50) NOT NULL,
                `create_at` DATETIME NOT NULL,
                `user_id` INT(11) NOT NULL,
                PRIMARY KEY (`model`, `model_id`, `tag_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ;

        ");
    }

    public function down()
    {
        echo "m150629_195019_tags cannot be reverted.\n";

        return false;
    }
}