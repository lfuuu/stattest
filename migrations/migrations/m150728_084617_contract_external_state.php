<?php

class m150728_084617_contract_external_state extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contract`
                CHANGE COLUMN `state` `state` ENUM('unchecked','checked_copy','checked_original', 'external') NULL DEFAULT 'unchecked' AFTER `contract_type_id`;
        ");
    }

    public function down()
    {
        echo "m150728_084617_contract_external_state cannot be reverted.\n";

        return false;
    }
}