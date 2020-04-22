<?php

use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m200106_140127_api_call_resource
 */
class m200106_140127_api_call_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        if (ResourceModel::findOne(['id' => ResourceModel::ID_API_CALL])) {
            return;
        }

        $this->insertResource(ServiceType::ID_BILLING_API_MAIN_PACKAGE, ResourceModel::ID_API_CALL, [
            'name' => 'Вызов API-метода',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ], [], false);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->deleteResource(ResourceModel::ID_API_CALL);
    }
}
