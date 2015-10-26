<?php

class m151019_084550_voip_number_hold_to extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `voip_numbers` 
            ADD COLUMN `hold_to` datetime NULL AFTER `hold_from`;
        ");

        $this->execute("UPDATE `voip_numbers` 
            SET hold_to = hold_from + INTERVAL 6 MONTH  WHERE status = 'hold'
            ");
    }

    public function down()
    {
        echo "m151019_084550_voip_number_hold_to cannot be reverted.\n";

        return false;
    }
}
