<?php

use app\classes\Migration;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffResource;

/**
 * Class m200724_091518_rename_res_43
 */
class m200724_091518_rename_res_43 extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        if (ResourceModel::findOne(['id' => ResourceModel::ID_VOIP_ROBOCALL])) {
            return true;
        }

        $this->insertResource(ServiceType::ID_VOIP, ResourceModel::ID_VOIP_ROBOCALL, [
            'name' => 'Автообзвон',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ], [
            \app\models\Currency::RUB => 0,
            \app\models\Currency::HUF => 0,
            \app\models\Currency::EUR => 0,
            \app\models\Currency::USD => 0,
        ], false); // + convert/usages/add-resource 2 53
    }

    /**
     * Down
     */
    public function safeDown()
    {
        if (!ResourceModel::findOne(['id' => ResourceModel::ID_VOIP_ROBOCALL])) {
            return true;
        }

        $this->delete(TariffResource::tableName(), [
            'resource_id' => [
                ResourceModel::ID_VOIP_ROBOCALL,
            ]
        ]);

        $this->delete(AccountTariffResourceLog::tableName(), [
            'resource_id' => [
                ResourceModel::ID_VOIP_ROBOCALL,
            ]
        ]);

        $this->delete(ResourceModel::tableName(), [
            'id' => [
                ResourceModel::ID_VOIP_ROBOCALL,
            ]
        ]);
    }
}
