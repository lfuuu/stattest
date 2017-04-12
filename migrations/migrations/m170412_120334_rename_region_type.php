<?php

use app\models\Region;

/**
 * Class m170412_120334_rename_region_type
 */
class m170412_120334_rename_region_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Region::tableName();
        $this->addColumn($tableName, 'is_active', $this->integer()->notNull()->defaultValue(1));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Region::tableName();
        $this->dropColumn($tableName, 'is_active');
    }
}
