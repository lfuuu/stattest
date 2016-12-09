<?php

use app\models\BusinessProcess;
use app\models\ContractType;

class m161122_090949_append_client_contract_type extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ContractType::tableName();

        $this->batchInsert($tableName, ['name', 'business_process_id'], [
            ['Внутрисетевой трафик', BusinessProcess::OPERATOR_CLIENTS,],
            ['Транзит VoIP', BusinessProcess::OPERATOR_CLIENTS,],
            ['Внутрисетевой трафик', BusinessProcess::OPERATOR_OPERATORS,],
            ['Транзит VoIP', BusinessProcess::OPERATOR_OPERATORS,],
        ]);
    }

    public function down()
    {
        $tableName = ContractType::tableName();

        $this->delete($tableName, [
            'AND',
            ['IN', 'business_process_id', [BusinessProcess::OPERATOR_CLIENTS, BusinessProcess::OPERATOR_OPERATORS,]],
            ['IN', 'name', ['Внутрисетевой трафик', 'Транзит VoIP',]]
        ]);
    }
}