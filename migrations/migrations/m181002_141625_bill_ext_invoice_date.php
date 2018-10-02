<?php

use app\models\BillExternal;

/**
 * Class m181002_141625_bill_ext_invoice_date
 */
class m181002_141625_bill_ext_invoice_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(BillExternal::tableName(), 'ext_invoice_date', $this->string());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BillExternal::tableName(), 'ext_invoice_date');
    }
}
