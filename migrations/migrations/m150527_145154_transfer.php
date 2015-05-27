<?php

class m150527_145154_transfer extends \app\classes\Migration
{
    public function up()
    {
        /*$this->execute("
            ALTER TABLE `usage_welltime`
	            ADD COLUMN `src_usage_id` INT(11) NULL DEFAULT '0' AFTER `router`,
                ADD COLUMN `dst_usage_id` INT(11) NULL DEFAULT '0' AFTER `router`;
        ");*/

        $this->execute("
            ALTER TABLE `usage_extra`
	            ADD COLUMN `src_usage_id` INT(11) NULL DEFAULT '0' AFTER `code`,
                ADD COLUMN `dst_usage_id` INT(11) NULL DEFAULT '0' AFTER `code`;
        ");

        /*
        $this->execute("
            ALTER TABLE `usage_emails`
	            ADD COLUMN `src_usage_id` INT(11) NULL DEFAULT '0' AFTER `status`,
                ADD COLUMN `dst_usage_id` INT(11) NULL DEFAULT '0' AFTER `status`;
        ");
        */
    }

    public function down()
    {
        //echo "m150527_145154_transfer cannot be reverted.\n";
        return true;
    }
}