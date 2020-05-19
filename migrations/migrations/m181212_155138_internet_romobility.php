<?php

use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m181212_155138_internet_romobility
 */
class m181212_155138_internet_romobility extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY,
            'name' => 'Телефония. Пакет интернета. Roamability.',
            'parent_id' => ServiceType::ID_VOIP,
            'close_after_days' => 60,
        ]);

        $this->insert(ResourceModel::tableName(), [
            'id' => ResourceModel::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY,
            'name' => 'Траффик',
            'unit' => 'Мб',
            'min_value' => 0,
            'max_value' => null,
            'service_type_id' => ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ResourceModel::tableName(), ['id' => ResourceModel::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY]);
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY]);
    }
}
