<?php

/**
 * Class m241004_141716_invoice_date
 */
class m241004_141716_invoice_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn('invoice', 'invoice_date', 'DATE NULL');
        $this->addColumn('newbills', 'invoice_date', 'DATE NULL');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('invoice', 'invoice_date');
        $this->dropColumn('newbills', 'invoice_date');
    }
}
