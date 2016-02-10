<?php

class m151215_170522_important_events_sources extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            INSERT INTO `important_events_sources`
                (`code`, `title`)
            VALUES
                ("stat", "MCN Stat"),
                ("billing", "Билинг"),
                ("core", "Ядро"),
                ("platform", "Платформа");
        ');
    }

    public function down()
    {
        echo "m151215_170522_important_events_sources cannot be reverted.\n";

        return false;
    }
}