<?php

use app\modules\uu\models\ServiceType;

/**
 * Class m190418_150612_billing_package
 */
class m190418_150612_billing_package extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_BILLING_API_MAIN_PACKAGE,
            'name' => 'Биллинг API. Основной пакет.',
            'parent_id' => ServiceType::ID_BILLING_API,
            'close_after_days' => 60,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_BILLING_API_MAIN_PACKAGE]);
    }
}
