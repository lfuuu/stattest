<?php

use yii\db\Migration;

class m160517_144422_create_view_for_dbro_and_api extends app\classes\Migration
{
    public function up()
    {
        $this->execute('
            CREATE VIEW view_client_struct_ro AS SELECT
                clients.client as clientIdent,
                client_super.id as id,
                client_super.name as name,
                clients.timezone_name as timezone,
                client_contragent.id as contragents_id,
                client_contragent.name as contragents_name,
                country.alpha_3 as contragents_country,
                clients.id as contragents_accounts_id,
                (client_contract.business_id IS NULL) as contragents_accounts_is_partner,
                (client_contract.business_process_status_id = 9 AND client_contract.business_id = 2) AS is_disabled
            FROM clients
            LEFT JOIN client_contract ON clients.contract_id = client_contract.id
            LEFT JOIN client_contragent ON client_contragent.id = client_contract.contragent_id
            LEFT JOIN client_super ON client_super.id = client_contragent.super_id
            LEFT JOIN country ON clients.country_id = country.code;
        ');
        $this->execute("
            CREATE VIEW view_platforma_services_ro AS
                      SELECT client, id, 'phone' as name , (actual_from <= NOW() AND actual_to >= NOW()) as enabled FROM usage_voip
            UNION ALL SELECT client, id, 'vpbx' as name, (actual_from <= NOW() AND actual_to >= NOW()) as enabled FROM usage_virtpbx
            UNION ALL SELECT client, id, 'feedback' as name, (actual_from <= NOW() AND actual_to >= NOW()) as enabled from usage_call_chat;
        ");
    }

    public function down()
    {
        $this->execute('DROP VIEW view_client_struct_ro;');
        $this->execute('DROP VIEW view_platforma_services_ro;');
    }
}
