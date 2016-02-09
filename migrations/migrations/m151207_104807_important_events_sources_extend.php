<?php

class m151207_104807_important_events_sources_extend extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('truncate important_events_sources');

        $this->execute('
            ALTER TABLE `important_events_sources`
                ADD COLUMN `code` VARCHAR(50) NOT NULL AFTER `id`,
                ADD UNIQUE INDEX `code` (`code`);
        ');
    }

    public function down()
    {
        echo "m151207_104807_important_events_sources_extend cannot be reverted.\n";

        return false;
    }
}
