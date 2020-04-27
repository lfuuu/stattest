<?php

use app\models\Invoice;

/**
 * Class m200417_102334_invoice_pay_bill_until
 */
class m200417_102334_invoice_pay_bill_until extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'pay_bill_until', $this->date()->defaultValue(null));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'pay_bill_until');
    }
}
