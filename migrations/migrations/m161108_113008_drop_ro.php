<?php

class m161108_113008_drop_ro extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('DROP VIEW IF EXISTS view_client_account_contacts_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_account_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_contract_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_super_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_call_chat_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_emails_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_extra_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_ip_port_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_ip_routes_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_sms_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_tech_cpe_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_trunk_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_virtpbx_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_voip_package_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_voip_ro');
        $this->execute('DROP VIEW IF EXISTS view_client_usage_welltime_ro');
        $this->execute('DROP VIEW IF EXISTS view_country_ro');
        $this->execute('DROP VIEW IF EXISTS view_important_events_names_ro');
        $this->execute('DROP VIEW IF EXISTS view_important_events_ro');
        $this->execute('DROP VIEW IF EXISTS view_message_templates_events_ro');
        $this->execute('DROP VIEW IF EXISTS view_platforma_services_ro');
        $this->execute('DROP VIEW IF EXISTS view_regions_ro');
    }

    public function down()
    {
    }
}
