<?php

class m150717_072558_client_document extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
ALTER TABLE `client_document`
	CHANGE COLUMN `contract_id` `contract_id` INT(11) NOT NULL AFTER `id`,
	ADD COLUMN `account_id` INT NULL AFTER `contract_id`;

	UPDATE `client_document` SET account_id = contract_id WHERE type = 'blank';
        ");
    }

    public function down()
    {
        echo "m150717_072558_client_document cannot be reverted.\n";

        return false;
    }
}