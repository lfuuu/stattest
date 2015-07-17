<?php

class m150717_135416_news extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
CREATE TABLE `news` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`message` TEXT NOT NULL,
	`to_user_id` INT(11) NULL DEFAULT '0',
	`date` DATETIME NOT NULL,
	`priority` ENUM('unimportant','usual','important') NOT NULL DEFAULT 'usual',
	PRIMARY KEY (`id`),
	INDEX `date` (`date`),
	INDEX `to_user_id` (`to_user_id`),
	INDEX `priority` (`priority`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=46
;

        ");
    }

    public function down()
    {
        echo "m150717_135416_news cannot be reverted.\n";

        return false;
    }
}