<?php

class m150720_145223_removeClientSelect extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            DROP VIEW IF EXISTS `clients_select`;
        ");
    }

    public function down()
    {
        echo "m150720_145223_removeClientSelect cannot be reverted.\n";

        return false;
    }
}