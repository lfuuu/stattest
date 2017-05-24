<?php
use app\models\UsageVirtpbx;

/**
 * Class m170523_175248_usage_voip_is_dearchived
 */
class m170523_175248_usage_virtpbx_is_dearchived extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageVirtpbx::tableName(), 'is_dearchived', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(UsageVirtpbx::tableName(), 'is_dearchived');
    }
}
