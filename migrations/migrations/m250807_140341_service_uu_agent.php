<?php

use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;


/**
 * Class m250807_140341_service_uu_agent
 */
class m250807_140341_service_uu_agent extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_AI_AGENT,
            'name' => 'ИИ-агент',
            'parent_id' => null,
            'close_after_days' => 60
        ]);

        $this->insertResource(ServiceType::ID_AI_AGENT,
            ResourceModel::ID_AI_AGENT_QTY, [
                'name' => 'Количество',
                'unit' => '¤',
                'min_value' => 1,
                'max_value' => 100,
            ]);

    }


    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ResourceModel::tableName(), ['id' => [ResourceModel::ID_AI_AGENT_QTY]]);
        $this->delete(ServiceType::tableName(), ['id' => [ServiceType::ID_AI_AGENT]]);
    }
}