<?php

class m151112_125602_append_contract_type extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            INSERT INTO `client_contract_type`
                (`name`, `business_process_id`)
            VALUES
                ("Агентский на 8 800", 11),
                ("Клиентский на 8 800", 11);
        ');
    }

    public function down()
    {
        echo "m151112_125602_append_contract_type cannot be reverted.\n";

        return false;
    }
}