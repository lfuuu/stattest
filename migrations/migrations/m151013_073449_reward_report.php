<?php

class m151013_073449_reward_report extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
                ADD COLUMN `sale_channel_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `comment`,
                ADD COLUMN `partner_id` INT UNSIGNED NULL DEFAULT NULL AFTER `sale_channel_id`;
        ");

        $this->execute("
            CREATE TABLE `sale_channel` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`id`)
            )
            ENGINE=InnoDB
            ;
        ");
    }

    public function down()
    {
        echo "m151013_073449_reward_report cannot be reverted.\n";

        return false;
    }
}