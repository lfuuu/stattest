<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m230421_061318_multichat_operator_count
 */
class m230421_061318_multichat_operator_count extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(
            ServiceType::ID_MULTICHAT,
            ResourceModel::ID_MULTICHAT_OPERATOR_COUNT,
            [
                'name' => 'Количество операторов',
                'unit' => '¤',
                'min_value' => 0,
                'max_value' => 100,
            ],
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->deleteResource(ResourceModel::ID_MULTICHAT_OPERATOR_COUNT);
    }
}
