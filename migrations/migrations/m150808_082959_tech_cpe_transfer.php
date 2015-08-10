<?php

class m150808_082959_tech_cpe_transfer extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `tech_cpe`
                ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `ast_autoconf`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");
    }

    public function down()
    {
        echo "m150808_082959_tech_cpe_transfer cannot be reverted.\n";

        return false;
    }
}