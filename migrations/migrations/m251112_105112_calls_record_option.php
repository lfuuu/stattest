<?php

use app\classes\Migration;
use app\models\Currency;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m251112_105112_calls_record_option
 */
class m251112_105112_calls_record_option extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_VOIP, ResourceModel::ID_VOIP_CALL_RECORDING, [
            'name' => 'Запись разговоров',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ],
            [
                Currency::RUB => 99,
                Currency::HUF => 400,
                Currency::EUR => 1,
                Currency::USD => 1,
            ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->deleteResource(ResourceModel::ID_VOIP_CALL_RECORDING);
    }
}
