<?php
use app\models\BusinessProcess;
use app\models\ContractType;

/**
 * Class m170606_152501_client_contract_type
 */
class m170606_152501_client_contract_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $contractTypeTableName = ContractType::tableName();

        $this->insert($contractTypeTableName, [
            'id' => 35,
            'name' => 'Совместное использование оборудования',
            'business_process_id' => BusinessProcess::OPERATOR_OPERATORS,
        ]);

        $this->insert($contractTypeTableName, [
            'id' => 36,
            'name' => 'Совместное использование оборудования',
            'business_process_id' => BusinessProcess::OPERATOR_CLIENTS,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $contractTypeTableName = ContractType::tableName();

        $this->delete($contractTypeTableName, [
            'IN', 'id', [35, 36]
        ]);
    }
}
