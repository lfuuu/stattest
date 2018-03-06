<?php

use app\models\EntryPoint;

/**
 * Class m180303_170014_entry_point_name_prefix
 */
class m180303_170014_entry_point_name_prefix extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->renameColumn(EntryPoint::tableName(), 'super_client_prefix', 'name_prefix');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->renameColumn(EntryPoint::tableName(), 'name_prefix', 'super_client_prefix');
    }
}
