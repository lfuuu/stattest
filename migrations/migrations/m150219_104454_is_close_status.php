<?php

class m150219_104454_is_close_status extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `grid_settings` ADD COLUMN `is_close_status`  tinyint(1) NOT NULL DEFAULT 0");
        $this->execute("update `grid_settings` set is_close_status =1 where oldstatus in ('closed','tech_deny','deny','debt','double','trash','move','denial','suspended')");
    }

    public function down()
    {
        echo "m150219_104454_is_close_status cannot be reverted.\n";

        return false;
    }
}
