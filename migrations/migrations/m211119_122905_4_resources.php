<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m211119_122905_4_resources
 */
class m211119_122905_4_resources extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_CHAT_BOT,
            ResourceModel::ID_CB_ADMIN, [
                'name' => 'Администрирование',
                'unit' => '',
                'min_value' => 0,
                'max_value' => 1,
            ], [
                \app\models\Currency::RUB => 1,
                \app\models\Currency::EUR => 1,
                \app\models\Currency::HUF => 1,
                \app\models\Currency::USD => 1,
            ]);

        $this->insertResource(ServiceType::ID_VOIP,
            ResourceModel::ID_VOIP_GEO_REPLACE, [
                'name' => 'Гео-Автозамена',
                'unit' => '',
                'min_value' => 0,
                'max_value' => 1,
            ], [
                \app\models\Currency::RUB => 1,
                \app\models\Currency::EUR => 1,
                \app\models\Currency::HUF => 1,
                \app\models\Currency::USD => 1,
            ]);

        $this->insertResource(ServiceType::ID_VPBX,
            ResourceModel::ID_VPBX_GEO_REPLACE, [
                'name' => 'Гео-Автозамена',
                'unit' => '',
                'min_value' => 0,
                'max_value' => 1,
            ], [
                \app\models\Currency::RUB => 1,
                \app\models\Currency::EUR => 1,
                \app\models\Currency::HUF => 1,
                \app\models\Currency::USD => 1,
            ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ResourceModel::tableName(), ['id' => [
            ResourceModel::ID_CB_ADMIN,
            ResourceModel::ID_VOIP_GEO_REPLACE,
            ResourceModel::ID_VPBX_GEO_REPLACE
        ]]);
    }
}
