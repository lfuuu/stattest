<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m210604_165433_service_a2p
 */
class m210604_165433_service_a2p extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_A2P,
            'name' =>  'A2P',
            'parent_id' => null,
            'close_after_days' => 60
        ]);

        $this->insertResource(ServiceType::ID_A2P, ResourceModel::ID_A2P_ALFA_NUMBERS, [
            'name' => 'Количество Альфа-номеров',
            'unit' => '¤',
            'min_value' => 1,
            'max_value' => 100,
        ]);

        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_A2P_PACKAGE,
            'name' =>  'A2P. Основной пакет',
            'parent_id' => null,
            'close_after_days' => 60
        ]);


        $this->insertResource(ServiceType::ID_A2P_PACKAGE, ResourceModel::ID_A2P_SMS, [
            'name' => 'SMS',
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
        $this->deleteResource(ResourceModel::ID_A2P_ALFA_NUMBERS);
        $this->deleteResource(ResourceModel::ID_A2P_SMS);
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_A2P]);
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_A2P_PACKAGE]);
    }
}
