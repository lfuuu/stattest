<?php

class m150210_151217_business_process_id extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `clients` ADD COLUMN `business_process_id`  int NOT NULL DEFAULT 0 AFTER `contract_type_id`");
        $this->execute("update clients c, (SELECT t.id as type_id, p.id as bp_id FROM client_contract_type t, `grid_business_process` p where p.client_contract_id=t.id and p.id !=2) b 
            set 
            c.business_process_id = b.bp_id
            where b.type_id = c.contract_type_id");
    }

    public function down()
    {
        echo "m150210_151217_business_process_id cannot be reverted.\n";

        return false;
    }
}
