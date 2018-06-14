<?php

use app\models\Number;

/**
 * Class m180601_114912_add_imsi_column_to_voip_numbers
 */
class m180601_114912_add_imsi_column_to_voip_numbers extends \app\classes\Migration
{
    private $_column = 'imsi';

    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Number::tableName();
        $this->addColumn($tableName, $this->_column, $this->bigInteger());
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
