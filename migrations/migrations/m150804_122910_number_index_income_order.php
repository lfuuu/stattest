<?php

class m150804_122910_number_index_income_order extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `g_income_order` ADD INDEX `number` (`number`)");
    }

    public function down()
    {
        echo "m150804_122910_number_index_income_order cannot be reverted.\n";

        return false;
    }
}