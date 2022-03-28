<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m220328_051408_voip_is_smart
 */
class m220328_051408_voip_is_smart extends Migration
{

    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_VOIP,
            ResourceModel::ID_VOIP_IS_SMART, [
                'name' => 'Умный (is smart)',
                'unit' => '',
                'min_value' => 0,
                'max_value' => 1,
            ], [
                \app\models\Currency::RUB => 0,
                \app\models\Currency::EUR => 0,
                \app\models\Currency::HUF => 0,
                \app\models\Currency::USD => 0,
            ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ResourceModel::tableName(), ['id' => [
            ResourceModel::ID_VOIP_IS_SMART,
        ]]);
    }
}
