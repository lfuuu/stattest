<?php

class m151125_153528_important_events_rules extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `important_events_rules` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(150) NOT NULL DEFAULT '0',
                `property` VARCHAR(30) NULL DEFAULT NULL,
                `condition` ENUM('==','!=','<=','>=','<','>','isset') NOT NULL DEFAULT '==',
                `value` VARCHAR(50) NULL DEFAULT NULL,
                `action` VARCHAR(30) NULL DEFAULT NULL,
                `message_template_id` INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `important_events_rules__message_template_id` (`message_template_id`),
                CONSTRAINT `important_events_rules__message_template_id` FOREIGN KEY (`message_template_id`) REFERENCES `message_template` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
            ) COLLATE='utf8_general_ci' ENGINE=InnoDB
        ");
    }

    public function down()
    {
        echo "m151125_153528_important_events_rules cannot be reverted.\n";

        return false;
    }
}