<?php

class m150812_101820_grid_business_process_status_fix_duplicate extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE `client_contract_business_process_status`
                SET `business_process_id` = 14
            WHERE `id` IN(77,78,79,80,81,82,83,84,124);
        ");
    }

    public function down()
    {
        echo "m150812_101820_grid_business_process_status_fix_duplicate cannot be reverted.\n";

        return false;
    }
}