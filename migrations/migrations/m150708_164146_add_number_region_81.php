<?php

class m150708_164146_add_number_region_81 extends \app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile("voip_numbers.sql");

    }

    public function down()
    {
        echo "m150708_164146_add_number_region_81 cannot be reverted.\n";

        return false;
    }
}

