<?php

class m150720_114301_voip_prefixlist_type_field extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `voip_prefixlist`
              ADD COLUMN `sub_type` ENUM('all','fixed','mobile') NOT NULL DEFAULT 'all' AFTER `type_id`;
        ");
    }

    public function down()
    {
        echo "m150720_114301_voip_prefixlist_type_field cannot be reverted.\n";

        return false;
    }
}