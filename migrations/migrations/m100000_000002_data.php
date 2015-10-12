<?php

class m100000_000002_data extends app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('adsl_speed.sql');
        $this->executeSqlFile('client_contract_business_process.sql');
        $this->executeSqlFile('client_contract_business_process_status.sql');
        $this->executeSqlFile('client_contract_business.sql');
        $this->executeSqlFile('client_contract_type.sql');
        $this->executeSqlFile('code_opf.sql');
        $this->executeSqlFile('country.sql');
        $this->executeSqlFile('city.sql');
        $this->executeSqlFile('currency.sql');
        $this->executeSqlFile('datacenter.sql');
        $this->executeSqlFile('document_template.sql');
        $this->executeSqlFile('did_group.sql');
        $this->executeSqlFile('firma_pay_account.sql');
        $this->executeSqlFile('g_division.sql');
        $this->executeSqlFile('g_organization.sql');
        $this->executeSqlFile('g_price_type.sql');
        $this->executeSqlFile('g_store.sql');
        $this->executeSqlFile('g_unit.sql');
        $this->executeSqlFile('language.sql');
        $this->executeSqlFile('metro.sql');
        $this->executeSqlFile('migration.sql');
        $this->executeSqlFile('modules.sql');
        $this->executeSqlFile('okvd.sql');
        $this->executeSqlFile('person.sql');
        $this->executeSqlFile('organization.sql');
        $this->executeSqlFile('regions.sql');
        $this->executeSqlFile('tech_nets.sql');
        $this->executeSqlFile('tt_folders.sql');
        $this->executeSqlFile('tt_states_o.sql');
        $this->executeSqlFile('tt_states.sql');
        $this->executeSqlFile('tt_types.sql');
        $this->executeSqlFile('user_departs.sql');
        $this->executeSqlFile('user_rights.sql');
        $this->executeSqlFile('user_grant_groups.sql');
        $this->executeSqlFile('user_groups.sql');
        $this->executeSqlFile('user_users.sql');
        $this->executeSqlFile('voip_numbers.sql');
        $this->executeSqlFile('tarifs_voip.sql');
        $this->executeSqlFile('tarifs_number.sql');
    }

    public function down()
    {
        echo "m100000_000002_data cannot be reverted.\n";

        return false;
    }
}

