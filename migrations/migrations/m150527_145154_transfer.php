<?php

class m150527_145154_transfer extends \app\classes\Migration
{
    public function up()
    {

        $this->execute("
            ALTER TABLE `usage_extra`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `code`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_welltime`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `router`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `emails`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `status`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_sms`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `tarif_id`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_voip`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `tmp`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_ip_ports`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `amount`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_virtpbx`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `moved_from`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_trunk`
	            ADD COLUMN `prev_usage_id` INT(11) NULL DEFAULT '0' AFTER `tmp`,
                ADD COLUMN `next_usage_id` INT(11) NULL DEFAULT '0' AFTER `prev_usage_id`;
        ");

    }

    public function down()
    {
        $this->execute("
            ALTER TABLE `usage_welltime`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_extra`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `emails`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_sms`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_voip`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_ip_ports`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_virtpbx`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        $this->execute("
            ALTER TABLE `usage_trunk`
	            DROP COLUMN `prev_usage_id`,
	            DROP COLUMN `next_usage_id`;
        ");

        return true;
    }
}