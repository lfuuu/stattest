<?php

class m160407_155712_view_for_client_contract extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
	    CREATE VIEW view_client_contract_ro AS SELECT id    , super_id , contragent_id , number , organization_id , manager , account_manager , business_id , business_process_id , business_process_status_id , contract_type_id , state        , financial_type , federal_district , is_external , lk_access FROM client_contract;
        ");
    }

    public function down()
    {
        $this->execute("DROP VIEW view_client_contract_ro;");
    }
}