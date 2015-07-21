<?php

class m150721_121436_updateStatuses extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          UPDATE client_contract SET business_process_status_id = 19, business_process_status_id = 1 WHERE (contract_type_id = 2) AND (business_process_status_id = 0);
        ");
    }

    public function down()
    {
        echo "m150721_121436_updateStatuses cannot be reverted.\n";

        return false;
    }
}