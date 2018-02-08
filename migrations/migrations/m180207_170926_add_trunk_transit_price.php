<?php

use app\models\UsageTrunk;

/**
 * Class m180207_170926_add_trunk_transit_price
 */
class m180207_170926_add_trunk_transit_price extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageTrunk::tableName(), 'transit_price', $this->decimal(13, 4)->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(UsageTrunk::tableName(), 'transit_price');
    }
}
