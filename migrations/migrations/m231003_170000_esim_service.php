<?php

use app\classes\Migration;
use app\modules\uu\models\ServiceType;

/**
 * Class m231003_170000_esim_service
 */
class m231003_170000_esim_service extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ServiceType::tableName(), ['id' => ServiceType::ID_ESIM, 'name' => '(e)SIM']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ServiceType::tableName(), ['id' => ServiceType::ID_ESIM]);
    }
}
