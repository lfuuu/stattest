<?php

use app\models\Bill;

/**
 * Class m180709_134657_bill_to_sf
 */
class m180709_134657_bill_to_sf extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Bill::tableName(), 'is_to_uu_invoice', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Bill::tableName(), 'is_to_uu_invoice');
    }
}
