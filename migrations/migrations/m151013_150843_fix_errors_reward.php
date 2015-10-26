<?php

class m151013_150843_fix_errors_reward extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contract_reward`
                ALTER `usage_type` DROP DEFAULT;
        ");
        $this->execute("
            ALTER TABLE `client_contract_reward`
                CHANGE COLUMN `usage_type` `usage_type` ENUM('voip','virtpbx') NOT NULL AFTER `contract_id`;
        ");
    }

    public function down()
    {
        echo "m151013_150843_fix_errors_reward cannot be reverted.\n";

        return false;
    }
}