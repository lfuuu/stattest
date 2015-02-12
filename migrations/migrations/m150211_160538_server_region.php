<?php

class m150211_160538_server_region extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `datacenter`  ADD COLUMN `region`     int(4) NOT NULL DEFAULT 99");
        $this->execute("ALTER TABLE `tt_troubles` ADD COLUMN `server_id`  int(4) NOT NULL DEFAULT 0");
        $this->execute("ALTER TABLE `tt_troubles` ADD INDEX `server_id` (`server_id`)");

    }

    public function down()
    {
        echo "m150211_160538_server_region cannot be reverted.\n";

        return false;
    }
}
