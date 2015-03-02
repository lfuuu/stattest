<?php

class m150302_122633_debt_is_not_close_status extends \app\classes\Migration
{
    public function up()
    {   
        $this->execute("update grid_settings set is_close_status = 0 where oldstatus = 'debt'"); //debt=Отключен за долги
        $this->execute("update clients set is_blocked = 1, is_active = 1 where business_process_status_id = 11"); //id:11 = Отключен за долги

    }

    public function down()
    {
        echo "m150302_122633_debt_is_not_close_status cannot be reverted.\n";

        return false;
    }
}
