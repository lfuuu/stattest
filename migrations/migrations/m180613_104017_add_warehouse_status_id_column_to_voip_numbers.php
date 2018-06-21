<?php

use app\models\Number;

/**
 * Class m180613_104017_add_warehouse_status_id_column_to_voip_numbers
 */
class m180613_104017_add_warehouse_status_id_column_to_voip_numbers extends \app\classes\Migration
{
    private $_column = 'warehouse_status_id';

    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Number::tableName();
        $this->addColumn($tableName, $this->_column, $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Number::tableName();
        $this->dropColumn($tableName, $this->_column);
    }
}
