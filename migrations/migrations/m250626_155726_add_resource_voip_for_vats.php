<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m250626_155726_add_resource_voip_for_vats
 */
class m250626_155726_add_resource_voip_for_vats extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_VOIP, ResourceModel::ID_VOIP_ONLY_FOR_TRUNK_VATS, [
            'name' => 'Только для ВАТС/транк',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->deleteResource(ResourceModel::ID_VOIP_ONLY_FOR_TRUNK_VATS);
    }
}
