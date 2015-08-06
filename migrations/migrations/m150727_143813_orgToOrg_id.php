<?php

class m150727_143813_orgToOrg_id extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile("organizationIdToContract.sql");
    }

    public function down()
    {
        echo "m150727_143813_orgToOrg_id cannot be reverted.\n";

        return false;
    }
}