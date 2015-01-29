<?php

class m100000_000002_data extends app\classes\Migration
{
    public function up()
    {
        $this->executeSqlFile('adsl_speed.sql');
        $this->executeSqlFile('country.sql');
        $this->executeSqlFile('currency.sql');
        $this->executeSqlFile('datacenter.sql');
        $this->executeSqlFile('g_price_type.sql');
        $this->executeSqlFile('g_store.sql');
        $this->executeSqlFile('g_unit.sql');
        $this->executeSqlFile('grid_business_process.sql');
        $this->executeSqlFile('grid_business_process_statuses.sql');
        $this->executeSqlFile('grid_settings.sql');
        $this->executeSqlFile('metro.sql');
        $this->executeSqlFile('okvd.sql');
        $this->executeSqlFile('organization.sql');
        $this->executeSqlFile('organizations.sql');
        $this->executeSqlFile('regions.sql');
        $this->executeSqlFile('server_pbx.sql');
        $this->executeSqlFile('tech_cpe_models.sql');
        $this->executeSqlFile('tech_nets.sql');
        $this->executeSqlFile('tt_folders.sql');
        $this->executeSqlFile('tt_states.sql');
        $this->executeSqlFile('tt_types.sql');
        $this->executeSqlFile('user_departs.sql');
    }

    public function down()
    {
        echo "m100000_000002_data cannot be reverted.\n";

        return false;
    }
}
