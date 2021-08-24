<?php

/**
 * Class m210820_122802_region_vpbx
 */
class m210820_122802_region_vpbx extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Region::tableName(), 'is_use_vpbx', $this->integer()->notNull()->defaultValue(0));
        $this->update(\app\models\Region::tableName(), ['is_use_vpbx' => 1]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Region::tableName(), 'is_use_vpbx');
    }
}
