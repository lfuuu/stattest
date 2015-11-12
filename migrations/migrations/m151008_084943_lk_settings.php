<?php

use app\models\notifications\NotificationSettings;
use app\models\LkClientSettings;
use app\models\LkNoticeSetting;

class m151008_084943_lk_settings extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `notification_log`');

        $this->execute('
            CREATE TABLE `notification_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `client_id` INT(11) NOT NULL DEFAULT "0",
                `event` VARCHAR(150) NULL DEFAULT NULL,
                `is_set` TINYINT(4) NOT NULL DEFAULT "1" COMMENT "is set, or reset limit",
                `balance` DECIMAL(11,2) NOT NULL DEFAULT "0.00" COMMENT "client balance",
                `limit` INT(11) NOT NULL DEFAULT "0",
                `value` DECIMAL(11,2) NOT NULL COMMENT "payment sum value",
                PRIMARY KEY (`id`),
                INDEX `client_id` (`date`, `client_id`) USING BTREE,
                INDEX `date` (`date`) USING BTREE
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB;
        ');

        $this->execute('DROP TABLE IF EXISTS `notification_contact_log`');

        $this->execute('
            CREATE TABLE `notification_contact_log` (
                `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `contact_id` INT(11) NULL DEFAULT NULL,
                `notification_id` INT(11) NULL DEFAULT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT "0",
                INDEX `contact_id_client_event_id` (`contact_id`, `notification_id`)
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB;
        ');

        $this->execute('
            INSERT INTO `notification_log`
              (`date`,`client_id`,`event`,`balance`,`limit`,`value`,`is_set`)
              (SELECT DISTINCT `date`,`client_id`,`event`,`balance`,`limit`,`value`,`is_set` FROM `lk_notice_log`);
        ');

        $this->execute('
            INSERT INTO `notification_contact_log`
            (
                SELECT DISTINCT nl.`date`,`contact_id`,nl.`id`,0 AS status FROM `notification_log` nl
                    INNER JOIN `lk_notice_log` USING (`date`,`client_id`,`event`,`is_set`,`balance`,`limit`,`value`)
                    WHERE `contact_id` != 0
            );
        ');

        return true;
    }

    public function down()
    {
        echo "m151008_084943_lk_settings cannot be reverted.\n";

        return true;
    }

}