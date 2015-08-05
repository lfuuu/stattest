<?php

class m150731_125238_tt_files extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `tt_files` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `trouble_id` INT(11) NOT NULL,
                `user_id` INT(11) NOT NULL DEFAULT '0',
                `ts` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `comment` TEXT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `trouble_id` (`trouble_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down()
    {
        echo "m150731_125238_media_files cannot be reverted.\n";

        return false;
    }
}