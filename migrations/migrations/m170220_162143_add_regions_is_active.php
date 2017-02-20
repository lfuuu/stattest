<?php
use app\models\Region;

/**
 * Class m170220_162143_add_regions_is_active
 */
class m170220_162143_add_regions_is_active extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Region::tableName();
        $this->addColumn($tableName, 'is_active', $this->integer()->notNull()->defaultValue(0));
        $this->createIndex('idx-is_active', $tableName, 'is_active');

        Region::updateAll(
            ['is_active' => 1],
            ['id' => [76, 77, 78, 79, 81, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99]]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Region::tableName();
        $this->dropIndex('idx-is_active', $tableName);
        $this->dropColumn($tableName, 'is_active');
    }
}
