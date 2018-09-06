<?php

use app\models\Bill;

/**
 * Class m180906_093608_ext_invoice_number
 */
class m180906_093608_ext_invoice_number extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Bill::tableName(), 'invoice_no_ext', $this->string(32));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Bill::tableName(), 'invoice_no_ext');
    }
}
