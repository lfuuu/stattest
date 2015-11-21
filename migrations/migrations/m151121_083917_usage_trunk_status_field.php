<?php

class m151121_083917_usage_trunk_status_field extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            ALTER TABLE `usage_trunk`
                ADD COLUMN `status` ENUM("connecting","working") NOT NULL DEFAULT "working" AFTER `expire_dt`;
        ');
    }

    public function down()
    {
        echo "m151121_083917_usage_trunk_status_field cannot be reverted.\n";

        return false;
    }
}