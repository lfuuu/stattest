<?php

class m150506_105343_message extends \app\classes\Migration
{
    public function up()
    {
        $this->execute(
                "CREATE TABLE `message` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `account_id` INT(11) NOT NULL,
                        `subject` VARCHAR(250) NOT NULL,
                        `created_at` DATETIME NOT NULL,
                        `is_read` TINYINT(1) NOT NULL DEFAULT '0',
                        PRIMARY KEY (`id`),
                        INDEX `date` (`created_at`),
                        INDEX `account_id` (`account_id`)
                )
                COLLATE='utf8_general_ci'
                ENGINE=InnoDB"
                );
    }

    public function down()
    {
        echo "m150506_105343_message cannot be reverted.\n";

        return false;
    }
}