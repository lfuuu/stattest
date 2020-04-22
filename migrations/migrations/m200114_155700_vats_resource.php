<?php


use app\models\Currency;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffResource;

/**
 * Class m200114_155700_vats_resource
 */
class m200114_155700_vats_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_VPBX, ResourceModel::ID_VPBX_VOICE_ASSISTANT, [
            'name' => 'Голосовой помощник',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ], [
            Currency::RUB => 1950,
            Currency::HUF => 9500,
            Currency::EUR => 35,
            Currency::USD => 39,
        ]);

        $this->insertResource(ServiceType::ID_VPBX, ResourceModel::ID_VPBX_ROBOT_CONTROLLER, [
            'name' => 'Робот-контролер',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ], [
            Currency::RUB => 950,
            Currency::HUF => 4900,
            Currency::EUR => 16,
            Currency::USD => 19,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(TariffResource::tableName(), [
            'resource_id' => [
                ResourceModel::ID_VPBX_VOICE_ASSISTANT,
                ResourceModel::ID_VPBX_ROBOT_CONTROLLER,
            ]
        ]);

        $this->delete(AccountTariffResourceLog::tableName(), [
            'resource_id' => [
                ResourceModel::ID_VPBX_VOICE_ASSISTANT,
                ResourceModel::ID_VPBX_ROBOT_CONTROLLER,
            ]
        ]);

        $this->delete(ResourceModel::tableName(), [
            'id' => [
                ResourceModel::ID_VPBX_VOICE_ASSISTANT,
                ResourceModel::ID_VPBX_ROBOT_CONTROLLER,
            ]
        ]);
    }
}
