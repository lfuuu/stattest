<?php

class m150811_125903_expansionContragent extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('expansion.sql');
    }

    public function down()
    {
        echo "m150811_125903_expansionContragent cannot be reverted.\n";

        return false;
    }
}