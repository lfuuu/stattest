<?php

use app\classes\Migration;
use app\modules\uu\models\ServiceType;

/**
 * Class m210129_105502_usage_chat_bot
 */
class m210129_105502_usage_chat_bot extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_CHAT_BOT,
            'name' =>  'Чат-бот',
            'parent_id' => null,
            'close_after_days' => 60
        ]);

        $this->insertResource(ServiceType::ID_CHAT_BOT, \app\modules\uu\models\ResourceModel::ID_BOT, [
            'name' => 'Количество ботов',
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
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_CHAT_BOT]);
    }
}
