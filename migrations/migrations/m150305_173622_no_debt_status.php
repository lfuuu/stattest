<?php

class m150305_173622_no_debt_status extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("update `grid_settings` set show_as_status=0, is_close_status=0 where oldstatus='debt'");
        $this->execute("update `clients` set business_process_status_id=9, is_blocked=1, is_active=1 where business_process_status_id=11");
    }

    public function down()
    {
        echo "m150305_173622_no_debt_status cannot be reverted.\n";

        return false;
    }
}
