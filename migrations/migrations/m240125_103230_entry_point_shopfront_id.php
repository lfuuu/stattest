<?php

use app\classes\Migration;
use app\models\EntryPoint;

/**
 * Class m240125_103230_entry_point_shopfront_id
 */
class m240125_103230_entry_point_shopfront_id extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(EntryPoint::tableName(), 'lk_shopfront_id', $this->string(32)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(EntryPoint::tableName(), 'lk_shopfront_id');
    }
}
