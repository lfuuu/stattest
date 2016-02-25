<?php

use app\models\BusinessProcess;

class m160217_153522_business_process_status_for_parthers extends \app\classes\Migration
{
    public function up()
    {
        $this->insert('client_contract_business_process_status', [
            'business_process_id' => BusinessProcess::PARTNER_MAINTENANCE,
            'name' => 'Переговоры',
            'sort' => 0,
        ]);

        $this->insert('client_contract_business_process_status', [
            'business_process_id' => BusinessProcess::PARTNER_MAINTENANCE,
            'name' => 'Ручной счет',
            'sort' => 2,
        ]);

        $this->insert('client_contract_business_process_status', [
            'business_process_id' => BusinessProcess::PARTNER_MAINTENANCE,
            'name' => 'Приостановлен',
            'sort' => 3,
        ]);

        $this->insert('client_contract_business_process_status', [
            'business_process_id' => BusinessProcess::PARTNER_MAINTENANCE,
            'name' => 'Расторгнут',
            'sort' => 4,
        ]);

        $this->insert('client_contract_business_process_status', [
            'business_process_id' => BusinessProcess::PARTNER_MAINTENANCE,
            'name' => 'Отказ',
            'sort' => 5,
        ]);

        $this->insert('client_contract_business_process_status', [
            'business_process_id' => BusinessProcess::PARTNER_MAINTENANCE,
            'name' => 'Мусор',
            'sort' => 6,
        ]);
    }

    public function down()
    {
    }
}