<?php

class m160302_115351_append_contract_type extends \app\classes\Migration
{
    public function up()
    {
        $this->insert('client_contract_type', [
            'name' => 'Межоператорский VoIP',
            'business_process_id' => \app\models\BusinessProcess::OPERATOR_CLIENTS,
        ]);
    }

    public function down()
    {
        $this->delete('client_contract_type', [
            'name' => 'Межоператорский VoIP',
            'business_process_id' => \app\models\BusinessProcess::OPERATOR_CLIENTS,
        ]);
    }
}