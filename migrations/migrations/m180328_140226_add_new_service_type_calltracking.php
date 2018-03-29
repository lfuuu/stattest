<?php

use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;

/**
 * Class m180328_140226_add_new_service_type_calltracking
 */
class m180328_140226_add_new_service_type_calltracking extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), [
            'id' => ServiceType::ID_CALLTRACKING,
            'name' => 'CallTracking',
        ]);

        $this->insert(Resource::tableName(), [
            'id' => Resource::ID_CALLTRACKING,
            'name' => 'Aрендовано минут',
            'min_value' => 0.005,
            'service_type_id' => ServiceType::ID_CALLTRACKING,
            'unit' => 'min',
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(Resource::tableName(), [
            'id' => Resource::ID_CALLTRACKING,
        ]);

        $this->delete(ServiceType::tableName(), [
            'id' => ServiceType::ID_CALLTRACKING,
        ]);
    }
}