<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m230823_141954_vpbx_transcrib
 */
class m230823_141954_vpbx_transcrib extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_VPBX,
            ResourceModel::ID_VPBX_TRANSCRIPTION, [
                'name' => 'Транскрибация',
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
            ResourceModel::ID_VPBX_TRANSCRIPTION
        ]]);
    }
}
