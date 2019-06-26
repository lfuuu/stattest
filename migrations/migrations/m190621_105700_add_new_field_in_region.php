<?php
use \app\models\Region;

/**
 * Class m190621_105700_add_new_field_in_region
 */
class m190621_105700_add_new_field_in_region extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Region::tableName(), 'is_use_sip_trunk', $this->integer()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Region::tableName(), 'is_use_sip_trunk');
    }
}
