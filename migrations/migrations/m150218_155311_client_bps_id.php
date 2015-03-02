<?php

class m150218_155311_client_bps_id extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `clients`
            ADD COLUMN `business_process_status_id`  int(11) NOT NULL DEFAULT 0 AFTER `business_process_id`
            ");

        $this->execute("update clients c 
            INNER JOIN grid_settings g ON (c.status = g.oldstatus AND g.show_as_status = 1) 
            set business_process_status_id = g.id 
            WHERE g.grid_business_process_id <>2");

        $this->execute("DROP TABLE `client_grid_statuses`");
        $this->execute("CREATE VIEW `client_grid_statuses` AS SELECT id AS client_id, business_process_status_id AS grid_status_id FROM `clients`");
    }

    public function down()
    {
        echo "m150218_155311_client_bps_id cannot be reverted.\n";

        return false;
    }
}
