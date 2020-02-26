<?php

use app\models\Currency;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffResource;

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
        if (Resource::findOne(['id' => Resource::ID_API_CALL])) {
            return;
        }

        $this->insertResource(ServiceType::ID_BILLING_API_MAIN_PACKAGE, Resource::ID_API_CALL, [
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
        $this->deleteResource(Resource::ID_API_CALL);
    }
}
