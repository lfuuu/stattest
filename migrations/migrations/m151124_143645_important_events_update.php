<?php

class m151124_143645_important_events_update extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `important_events`
                CHANGE COLUMN `client_id` `client_id` INT(11) NULL DEFAULT NULL,
                ADD COLUMN `extends_data` TEXT NULL COMMENT "JSON" AFTER `value`,
                ADD COLUMN `source` VARCHAR(16) NULL DEFAULT NULL AFTER `event`
                DROP COLUMN `is_set`,
                DROP COLUMN `balance`,
                DROP COLUMN `limit`,
                DROP COLUMN `value`
                DROP INDEX `client_id`;
        ');
    }

    public function down()
    {
        echo "m151124_143645_important_events_update cannot be reverted.\n";

        return false;
    }
}