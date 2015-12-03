<?php

class m151127_081124_important_events_all extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `important_events_rules_conditions` CASCADE');
        $this->execute('DROP TABLE IF EXISTS `important_events_properties` CASCADE');
        $this->execute('DROP TABLE IF EXISTS `important_events_rules` CASCADE');
        $this->execute('DROP TABLE IF EXISTS `important_events_names` CASCADE');
        $this->execute('DROP TABLE IF EXISTS `important_events_groups` CASCADE');
        $this->execute('DROP TABLE IF EXISTS `important_events`');

        $this->execute('
            CREATE TABLE `important_events` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `client_id` INT(11) NULL DEFAULT NULL,
                `event` VARCHAR(50) NULL DEFAULT NULL,
                `source` VARCHAR(16) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `date` (`date`) USING BTREE
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            CREATE TABLE `important_events_groups` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(150) NULL DEFAULT "0",
                PRIMARY KEY (`id`)
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            CREATE TABLE `important_events_names` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `code` VARCHAR(50) NOT NULL,
                `value` VARCHAR(150) NULL DEFAULT NULL,
                `group_id` INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `code_group_id` (`code`, `group_id`),
                INDEX `important_events_names__group_id` (`group_id`),
                CONSTRAINT `important_events_names__group_id` FOREIGN KEY (`group_id`) REFERENCES `important_events_groups` (`id`) ON DELETE SET NULL
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            CREATE TABLE `important_events_properties` (
                `event_id` INT(11) NOT NULL DEFAULT "0",
                `property` VARCHAR(30) NOT NULL DEFAULT "",
                `value` VARCHAR(150) NOT NULL DEFAULT "",
                PRIMARY KEY (`event_id`, `property`, `value`),
                CONSTRAINT `important_events_properties__event_id` FOREIGN KEY (`event_id`) REFERENCES `important_events` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            CREATE TABLE `important_events_rules` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(150) NOT NULL DEFAULT "",
                `action` VARCHAR(30) NULL DEFAULT NULL,
                `event` VARCHAR(50) NULL DEFAULT NULL,
                `message_template_id` INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `important_events_rules__message_template_id` (`message_template_id`),
                INDEX `important_events_rules__event` (`event`),
                CONSTRAINT `important_events_rules__event` FOREIGN KEY (`event`) REFERENCES `important_events_names` (`code`) ON DELETE SET NULL,
                CONSTRAINT `important_events_rules__message_template_id` FOREIGN KEY (`message_template_id`) REFERENCES `message_template` (`id`) ON DELETE SET NULL
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            CREATE TABLE `important_events_rules_conditions` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `rule_id` INT(11) NULL DEFAULT NULL,
                `property` VARCHAR(30) NULL DEFAULT NULL,
                `condition` ENUM("==","<>","<=",">=","<",">","isset") NOT NULL DEFAULT "isset",
                `value` VARCHAR(50) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `important_events_rules_conditions__rule_id` (`rule_id`),
                CONSTRAINT `important_events_rules_conditions__rule_id` FOREIGN KEY (`rule_id`) REFERENCES `important_events_rules` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            INSERT INTO `important_events_groups` (`id`, `title`) VALUES (1, "Базовая группа");
        ');

        $this->execute('
            INSERT INTO `important_events_names`
                (`code`, `value`, `group_id`)
            VALUES
                ("add_pay_notif", "Зачисление средств", 1),
                ("day_limit", "Суточный лимит", 1),
                ("min_balance", "Критический остаток", 1),
                ("unset_min_balance", "Снятие: Критический остаток", 1),
                ("unset_zero_balance", "Снятие: Финансовая блокировка", 1),
                ("zero_balance", "Финансовая блокировка", 1);
        ');
    }

    public function down()
    {
        echo "m151127_081124_important_events_rules_event cannot be reverted.\n";

        return false;
    }
}