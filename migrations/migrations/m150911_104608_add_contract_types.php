<?php

class m150911_104608_add_contract_types extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            INSERT INTO `client_contract_type` (`name`, `business_process_id`) VALUES ('Другой', 13);
            INSERT INTO `client_contract_type` (`name`, `business_process_id`) VALUES ('Договор на СОРМ', 13);
        ");
    }

    public function down()
    {
        echo "m150911_104608_add_contract_types cannot be reverted.\n";

        return false;
    }
}