<?php

class m151124_143645_important_events_update extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `important_events`
                CHANGE COLUMN `client_id` `client_id` INT(11) NULL DEFAULT NULL,
                ADD COLUMN `source` VARCHAR(16) NULL DEFAULT NULL AFTER `event`
                DROP COLUMN `is_set`,
                DROP COLUMN `balance`,
                DROP COLUMN `limit`,
                DROP COLUMN `value`
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
    }

    public function down()
    {
        echo "m151124_143645_important_events_update cannot be reverted.\n";

        return false;
    }
}