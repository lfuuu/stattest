<?php

class m150225_201217_status_color extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `grid_settings` ADD COLUMN `color` VARCHAR(16) NOT NULL DEFAULT '' AFTER `is_close_status`");

        $this->execute("update grid_settings set color='#C4DF9B' where oldstatus='negotiations'");
        $this->execute("update grid_settings set color='#6DCFF6' where oldstatus='testing'");
        $this->execute("update grid_settings set color='#F49AC1' where oldstatus='connecting'");
        $this->execute("update grid_settings set color='#FFFFCC' where oldstatus='closed'");
        $this->execute("update grid_settings set color='#996666' where oldstatus='tech_deny'");
        $this->execute("update grid_settings set color='#A0FFA0' where oldstatus='telemarketing'");
        $this->execute("update grid_settings set color='#CCFFFF' where oldstatus='income'");
        $this->execute("update grid_settings set color='#A0A0A0' where oldstatus='deny'");
        $this->execute("update grid_settings set color='#C00000' where oldstatus='debt'");
        $this->execute("update grid_settings set color='#60a0e0' where oldstatus='double'");
        $this->execute("update grid_settings set color='#a5e934' where oldstatus='trash'");
        $this->execute("update grid_settings set color='#f590f3' where oldstatus='move'");
        $this->execute("update grid_settings set color='#C4a3C0' where oldstatus='suspended'");
        $this->execute("update grid_settings set color='#00C0C0' where oldstatus='denial'");
        $this->execute("update grid_settings set color='silver' where oldstatus='once'");
        $this->execute("update grid_settings set color='silver' where oldstatus='reserved'");
        $this->execute("update grid_settings set color='silver' where oldstatus='blocked'");
        $this->execute("update grid_settings set color='yellow' where oldstatus='distr'");
        $this->execute("update grid_settings set color='lightblue' where oldstatus='operator'");
    }

    public function down()
    {
        echo "m150225_201217_status_color cannot be reverted.\n";

        return false;
    }
}
