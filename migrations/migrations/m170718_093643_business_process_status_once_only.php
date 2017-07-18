<?php
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;

/**
 * Class m170718_093643_business_process_status_once_only
 */
class m170718_093643_business_process_status_once_only extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $businessProcessStatusTableName = BusinessProcessStatus::tableName();

        $this->insert($businessProcessStatusTableName, [
            'id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_ONE_TIME,
            'business_process_id' => BusinessProcess::OPERATOR_INFRASTRUCTURE,
            'name' => 'Разовый',
            'sort' => 11,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $businessProcessStatusTableName = BusinessProcessStatus::tableName();

        $this->delete($businessProcessStatusTableName, [
            'id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_ONE_TIME,
        ]);
    }
}
