<?php

class m151022_113619_partner_contract_type extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            INSERT INTO `nispd`.`client_contract_type` (`name`, `business_process_id`) VALUES ('Разовый', 8);
            INSERT INTO `nispd`.`client_contract_type` (`name`, `business_process_id`) VALUES ('Постоянный', 8);
            INSERT INTO `nispd`.`client_contract_type` (`name`, `business_process_id`) VALUES ('Субоператорский', 8);
        ");
    }

    public function down()
    {
        echo "m151022_113619_partner_contract_type cannot be reverted.\n";

        return false;
    }
}