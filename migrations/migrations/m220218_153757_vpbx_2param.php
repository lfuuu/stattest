<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;

/**
 * Class m220218_153757_vpbx_2param
 */
class m220218_153757_vpbx_2param extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(\app\modules\uu\models\ServiceType::ID_VPBX,
            ResourceModel::ID_VPBX_SPECIAL_AUTOCALL, [
                'name' => 'СпецАвтообзвон',
                'unit' => '',
                'min_value' => 0,
                'max_value' => 1,
            ], [
                \app\models\Currency::RUB => 1,
                \app\models\Currency::EUR => 1,
                \app\models\Currency::HUF => 1,
                \app\models\Currency::USD => 1,
            ]);

        $this->insertResource(\app\modules\uu\models\ServiceType::ID_VPBX,
            ResourceModel::ID_VPBX_CALL_END_MANAGEMENT, [
                'name' => 'Упр.ЗавершениемЗвонка',
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
            ResourceModel::ID_VPBX_SPECIAL_AUTOCALL,
            ResourceModel::ID_VPBX_CALL_END_MANAGEMENT,
        ]]);
    }
}
