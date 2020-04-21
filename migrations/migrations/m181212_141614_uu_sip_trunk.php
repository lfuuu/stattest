<?php

use app\modules\uu\models\ResourceClass;
use app\modules\uu\models\ServiceType;

/**
 * Class m181212_141614_uu_sip_trunk
 */
class m181212_141614_uu_sip_trunk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        if (ServiceType::findOne(['id' => ServiceType::ID_SIPTRUNK])) {
            return;
        }

        $this->insert(ServiceType::tableName(), ['id' => ServiceType::ID_SIPTRUNK, 'name' => 'SIP-trunk', 'close_after_days' => ServiceType::CLOSE_AFTER_DAYS]);
        $this->insert(ResourceClass::tableName(), [
            'id' => ResourceClass::ID_CALLLIMIT,
            'name' => 'Call limit',
            'unit' => 'Unit',
            'min_value' => 0,
            'max_value' => 100,
            'service_type_id' => ServiceType::ID_SIPTRUNK,
        ]);

        $this->insert(ResourceClass::tableName(), [
            'id' => ResourceClass::ID_ALLOW_DIVERSION,
            'name' => 'Allow diversion',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
            'service_type_id' => ServiceType::ID_SIPTRUNK,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        if (!ServiceType::findOne(['id' => ServiceType::ID_SIPTRUNK])) {
            return;
        }

        $this->delete(ResourceClass::tableName(), [
            'id' => [ResourceClass::ID_CALLLIMIT, ResourceClass::ID_ALLOW_DIVERSION]
        ]);

        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_SIPTRUNK]);
    }
}
