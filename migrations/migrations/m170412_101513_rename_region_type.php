<?php
use app\models\Region;

/**
 * Class m170412_101513_rename_region_type
 */
class m170412_101513_rename_region_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Region::tableName();
        $this->renameColumn($tableName, 'is_active', 'type_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Region::tableName();
        $this->renameColumn($tableName, 'type_id', 'is_active');
    }
}
