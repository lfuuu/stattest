<?php

class m100000_000001_init extends app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('nispd.sql');
    }

    public function down()
    {
        echo "m100000_000001_init cannot be reverted.\n";

        return false;
    }
}
