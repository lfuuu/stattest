<?php

use app\classes\Migration;
use app\models\Number;

/**
 * Class m211103_181739_voip_number_client_id
 */
class m211103_181739_voip_number_client_id extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createIndex('voip_number-client_id', Number::tableName(), 'client_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('voip_number-client_id', Number::tableName());
    }
}
