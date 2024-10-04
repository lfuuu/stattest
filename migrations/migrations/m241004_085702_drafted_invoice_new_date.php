<?php

/**
 * Class m241004_085702_drafted_invoice_new_date
 */
class m241004_085702_drafted_invoice_new_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn('invoice', 'drafted_new_date', 'DATE NULL');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('invoice', 'drafted_new_date');
    }
}
