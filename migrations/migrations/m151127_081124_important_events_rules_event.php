<?php

class m151127_081124_important_events_rules_event extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `important_events_rules`
                ADD COLUMN `event` VARCHAR(50) NULL DEFAULT NULL AFTER `action`;
        ');
        $this->execute('
            ALTER TABLE `important_events_properties`
                DROP FOREIGN KEY `important_events_properties__event_id`;
        ');
    }

    public function down()
    {
        echo "m151127_081124_important_events_rules_event cannot be reverted.\n";

        return false;
    }
}