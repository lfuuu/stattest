<?php
use app\models\BusinessProcessStatus;
use app\models\BusinessProcess;
use yii\db\Expression;

/**
 * Class m170228_101442_business_process_status_manual_bill
 */
class m170228_101442_business_process_status_manual_bill extends \app\classes\Migration
{
    const STATUS_NAME = 'Ручной счет';

    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = BusinessProcessStatus::tableName();

        $this->insert($tableName, [
            'id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_MANUAL_BILL,
            'business_process_id' => BusinessProcess::OPERATOR_INFRASTRUCTURE,
            'name' => self::STATUS_NAME,
            'sort' => new Expression('((SELECT MAX(sort) FROM ' . $tableName . ' temp) + 1)'),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = BusinessProcessStatus::tableName();

        $this->delete($tableName, [
            'id' => BusinessProcessStatus::OPERATOR_INFRASTRUCTURE_MANUAL_BILL,
            'business_process_id' => BusinessProcess::OPERATOR_INFRASTRUCTURE,
            'name' => self::STATUS_NAME,
        ]);
    }
}
