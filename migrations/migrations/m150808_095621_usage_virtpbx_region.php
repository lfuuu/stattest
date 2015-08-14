<?php

class m150808_095621_usage_virtpbx_region extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `usage_virtpbx`
                ADD COLUMN `region` SMALLINT(6) NOT NULL AFTER `client`;
        ");

        $this->execute("
            UPDATE
                `usage_virtpbx` uv
                    LEFT JOIN `server_pbx` sp ON sp.`id` = uv.`server_pbx_id`
                        LEFT JOIN `datacenter` dc ON dc.`id` = sp.`datacenter_id`
            SET uv.`region` = dc.`region`;
        ");

        $this->execute("ALTER TABLE `usage_virtpbx` DROP COLUMN `server_pbx_id`;");
    }

    public function down()
    {
        echo "m150808_095621_usage_virtpbx_region cannot be reverted.\n";

        return false;
    }
}