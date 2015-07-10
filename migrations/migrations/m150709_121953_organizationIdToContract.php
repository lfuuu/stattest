<?php

class m150709_121953_organizationIdToContract extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile("organizationIdToContract.sql");
    }

    public function down()
    {
        echo "m150709_121953_organizationIdToContract cannot be reverted.\n";

        return false;
    }
}