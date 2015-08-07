<?php

class m150807_114302_client_is_external extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contract`
                CHANGE COLUMN `state` `state` ENUM('unchecked','checked_copy','checked_original') NULL DEFAULT 'unchecked' AFTER `contract_type_id`;
        ");
        $this->execute("
            ALTER TABLE `client_contract`
                ADD COLUMN `is_external` TINYINT(1) NOT NULL DEFAULT '0' AFTER `state`;
        ");
        $this->execute("
            UPDATE `client_contract` SET `is_external` = 1, `state` = 'unchecked' WHERE `state` = 'external';
        ");
    }

    public function down()
    {
        echo "m150807_114302_client_is_external cannot be reverted.\n";

        return false;
    }
}