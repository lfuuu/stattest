<?php

class m151124_143645_important_events_update extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            CREATE TABLE `important_events_names` (
                `code` VARCHAR(50) NULL DEFAULT NULL,
                `value` VARCHAR(150) NULL DEFAULT NULL,
                UNIQUE INDEX `code` (`code`)
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            ALTER TABLE `important_events`
                CHANGE COLUMN `client_id` `client_id` INT(11) NULL DEFAULT NULL,
                CHANGE COLUMN `event` `event` VARCHAR(50) NULL DEFAULT NULL,
                ADD COLUMN `source` VARCHAR(16) NULL DEFAULT NULL AFTER `event`,
                DROP COLUMN `is_set`,
                DROP COLUMN `balance`,
                DROP COLUMN `limit`,
                DROP COLUMN `value`,
                DROP INDEX `client_id`;
        ');

        $this->execute('
            CREATE TABLE `important_events_properties` (
                `event_id` INT(11) NULL DEFAULT NULL,
                `property` VARCHAR(30) NULL DEFAULT NULL,
                `value` VARCHAR(150) NULL DEFAULT NULL,
                INDEX `event_id_property_value` (`event_id`, `property`, `value`),
                CONSTRAINT `important_events_properties__event_id` FOREIGN KEY (`event_id`) REFERENCES `important_events` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
            ) COLLATE="utf8_general_ci" ENGINE=InnoDB
        ');

        $this->execute('
            INSERT INTO `important_events_names` (`code`, `value`)
            VALUES
                ("zero_balance", "Финансовая блокировка"),
                ("unset_zero_balance", "Снятие: Финансовая блокировка"),
                ("add_pay_notif", "Зачисление средств"),
                ("min_balance", "Критический остаток"),
                ("unset_min_balance", "Снятие: Критический остаток"),
                ("day_limit", "Суточный лимит");
        ');
    }

    public function down()
    {
        echo "m151124_143645_important_events_update cannot be reverted.\n";

        return false;
    }
}