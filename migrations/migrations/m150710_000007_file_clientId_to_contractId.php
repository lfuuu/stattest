<?php

class m150710_000007_file_clientId_to_contractId extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
UPDATE client_files cf
	LEFT JOIN clients c ON c.id = cf.client_id
	SET cf.client_id = c.contract_id;

DELETE FROM client_files WHERE client_id = 0;

ALTER TABLE `client_files`
	ALTER `client_id` DROP DEFAULT;
ALTER TABLE `client_files`
	CHANGE COLUMN `client_id` `contract_id` INT(11) NOT NULL AFTER `id`;
        ");
    }

    public function down()
    {
        echo "m150701_151359_file_clientId_to_contractId cannot be reverted.\n";

        return false;
    }
}