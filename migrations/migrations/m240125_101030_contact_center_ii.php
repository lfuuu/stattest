<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m240125_101030_contact_center_ii
 */
class m240125_101030_contact_center_ii extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(ServiceType::tableName(), ['name' => 'Контакт-центр ИИ'], ['id' => ServiceType::ID_CONTACT_CENTER_AI]);
        $this->insertResource(ServiceType::ID_CONTACT_CENTER_AI, ResourceModel::ID_CC_VOICE_ANALYTICS, [
            'name' => 'Голосовая Аналитика',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ]);

        $this->insertResource(ServiceType::ID_CONTACT_CENTER_AI, ResourceModel::ID_CC_RESOURCE1, [
            'name' => 'Ресурс 1',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ]);

        $this->insertResource(ServiceType::ID_CONTACT_CENTER_AI, ResourceModel::ID_CC_RESOURCE2, [
            'name' => 'Ресурс 2',
            'unit' => '',
            'min_value' => 0,
            'max_value' => 1,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(ServiceType::tableName(), ['name' => 'Мультичат'], ['id' => ServiceType::ID_CONTACT_CENTER_AI]);

        foreach ([ResourceModel::ID_CC_VOICE_ANALYTICS, ResourceModel::ID_CC_RESOURCE1, ResourceModel::ID_CC_RESOURCE2] as $resourceId) {
            $this->deleteResource($resourceId);
        }
    }
}
