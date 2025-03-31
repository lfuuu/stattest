<?php

/**
 * Class m250331_140059_resource_log_big_id
 */
class m250331_140059_resource_log_big_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\modules\uu\models\AccountLogResource::tableName(), 'id', 'bigint unsigned NOT NULL AUTO_INCREMENT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\modules\uu\models\AccountLogResource::tableName(), 'id', 'int NOT NULL AUTO_INCREMENT');
    }
}
