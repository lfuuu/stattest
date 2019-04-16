<?php

use app\modules\uu\models\ServiceType;

/**
 * Class m190415_155859_billing_api
 */
class m190415_155859_billing_api extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        if (ServiceType::findOne(['id' => ServiceType::ID_BILLING_API])) {
            return true;
        }

        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_BILLING_API,
            'name' => 'Биллинг API',
            'close_after_days' => ServiceType::CLOSE_AFTER_DAYS
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        if (!ServiceType::findOne(['id' => ServiceType::ID_BILLING_API])) {
            return true;
        }

        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_BILLING_API]);
    }
}
