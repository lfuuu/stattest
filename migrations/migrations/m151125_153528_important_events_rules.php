<?php

class m151125_153528_important_events_rules extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            CREATE TABLE `important_events_rules` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(150) NOT NULL DEFAULT "0",
                `action` VARCHAR(30) NULL DEFAULT NULL,
                `message_template_id` INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `important_events_rules__message_template_id` (`message_template_id`),
                CONSTRAINT `important_events_rules__message_template_id` FOREIGN KEY (`message_template_id`) REFERENCES `message_template` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            CREATE TABLE `important_events_rules_conditions` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `rule_id` INT(11) NULL DEFAULT NULL,
                `property` VARCHAR(30) NULL,
                `condition` ENUM("==","!=","<=",">=","<",">","isset") NOT NULL DEFAULT "isset",
                `value` VARCHAR(50) NULL,
                PRIMARY KEY (`id`),
                INDEX `important_events_rules_conditions__rule_id` (`rule_id`),
                CONSTRAINT `important_events_rules_conditions__rule_id` FOREIGN KEY (`rule_id`) REFERENCES `important_events_rules` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');
    }

    public function down()
    {
        echo "m151125_153528_important_events_rules cannot be reverted.\n";

        return false;
    }
}