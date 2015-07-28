<?php

class m150727_110648_recoveryLostData extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('contragent.sql');
        $this->executeSqlFile('contract.sql');
        $this->executeSqlFile('client.sql');
    }

    public function down()
    {
        echo "m150727_110648_recoveryLostData cannot be reverted.\n";

        return false;
    }
}