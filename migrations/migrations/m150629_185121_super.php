<?php

class m150629_185121_super extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
ALTER TABLE `client_super`
	CHANGE COLUMN `account_manager` `financial_manager_id` INT NOT NULL DEFAULT '0' AFTER `name`;
	");
    }

    public function down()
    {
        echo "m150629_185121_super cannot be reverted.\n";

        return false;
    }
}