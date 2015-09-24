<?php

class m150911_113244_docs extends \app\classes\Migration
{

    public function up()
    {
        $this->execute("
            CREATE TABLE `document_folder` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                `sort` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            )
            ENGINE=InnoDB
            ;
        ");
        $this->execute("
            CREATE TABLE `document_template` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                `folder_id` TINYINT(3) UNSIGNED NOT NULL,
                `content` TEXT NOT NULL,
                `type` ENUM('contract','agreement','blank') NOT NULL DEFAULT 'contract',
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ;
        ");

        /*
        $this->execute("
            DROP TABLE IF EXISTS `contract`;
        ");
        */
    }

    public function down()
    {
        echo "m150911_113244_docs cannot be reverted.\n";

        return false;
    }

}