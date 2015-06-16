<?php

class m150519_190430_exportClientsLogAndHistory extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('contragent.sql');
        $this->executeSqlFile('contract.sql');
        $this->executeSqlFile('client.sql');
    }

    public function down()
    {
        echo "m150519_190430_exportClientsLogAndHistory cannot be reverted.\n";

        return false;
    }
}