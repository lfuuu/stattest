<?php

class m150831_144253_is_external_to_contract extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contract`
	          ADD COLUMN `is_external` ENUM('internal','external') NOT NULL DEFAULT 'internal' AFTER `federal_district`;

            ALTER TABLE `client_document`
                DROP COLUMN `is_external`;

        ");
    }

    public function down()
    {
        echo "m150831_144253_is_external_to_contract cannot be reverted.\n";

        return false;
    }
}