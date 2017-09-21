<?php
use app\models\UsageTrunk;

/**
 * Class m170920_141125_trunk_ip
 */
class m170920_141125_trunk_ip extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageTrunk::tableName(), 'ip', $this->string(16));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(UsageTrunk::tableName(), 'ip');
    }
}
