<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m211015_135921_usage_voice_assist
 */
class m211015_135921_usage_voice_assist extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_VOICE_ROBOT,
            'name' => 'Голосовой робот',
            'parent_id' => null,
            'close_after_days' => 60
        ]);

        $this->insertResource(ServiceType::ID_VOICE_ROBOT,
            ResourceModel::ID_VR_CHANNEL_COUNT, [
                'name' => 'Канальность',
                'unit' => '¤',
                'min_value' => 1,
                'max_value' => 100,
            ]);

        $this->insertResource(ServiceType::ID_VOICE_ROBOT,
            ResourceModel::ID_VR_CAROUSEL, [
                'name' => 'Карусель',
                'unit' => '',
                'min_value' => 0,
                'max_value' => 1,
            ]);

        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_MULTICHAT,
            'name' => 'Мультичат',
            'parent_id' => null,
            'close_after_days' => 60
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ResourceModel::tableName(), ['id' => [ResourceModel::ID_VR_CHANNEL_COUNT, ResourceModel::ID_VR_CAROUSEL]]);
        $this->delete(ServiceType::tableName(), ['id' => [ServiceType::ID_VOICE_ROBOT, ServiceType::ID_MULTICHAT]]);
    }
}
