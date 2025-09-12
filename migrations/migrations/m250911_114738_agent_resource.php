<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\models\Currency;

/**
 * Class m250911_114738_agent_resource
 */
class m250911_114738_agent_resource extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(ServiceType::ID_AI_AGENT,
            ResourceModel::ID_AI_DIALOGUE_DURATION, [
                'name' => 'Длительность диалогов',
                'unit' => '¤',
                'min_value' => 0,
                'max_value' => null,
            ], [
                Currency::RUB => 6,
                Currency::HUF => 23.76,
                Currency::EUR => 0.06,
                Currency::USD => 0.07,
                Currency::KZT => 37.92,
            ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ResourceModel::tableName(), ['id' => [ResourceModel::ID_AI_DIALOGUE_DURATION]]);
    }
}
