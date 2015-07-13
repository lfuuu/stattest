<?php

class m150710_000008_documents extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE client_document cd
                INNER JOIN clients c ON c.id = cd.client_id
                SET cd.client_id = c.contract_id;

            ALTER TABLE `client_document`
                ALTER `client_id` DROP DEFAULT;
            ALTER TABLE `client_document`
                CHANGE COLUMN `client_id` `contract_id` INT(11) NOT NULL AFTER `id`;
        ");
    }

    public function down()
    {
        echo "m150707_165251_documents cannot be reverted.\n";

        return false;
    }
}