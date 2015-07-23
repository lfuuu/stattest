<?php

class m150723_133516_deleteContragent_idFromClients extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `clients` DROP COLUMN `contragent_id`;
        ");
    }

    public function down()
    {
        echo "m150723_133516_deleteContragent_idFromClients cannot be reverted.\n";

        return false;
    }
}