<?php

use app\classes\Migration;
use app\models\BillLine;
use app\models\Invoice;
use app\models\InvoiceLine;

/**
 * Class m200721_113654_invoice_lines_link
 */
class m200721_113654_invoice_lines_link extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(InvoiceLine::tableName(), 'line_id', $this->integer(10)->unsigned());

        $this->addForeignKey('fk-'.InvoiceLine::tableName().'-line_Id',
            InvoiceLine::tableName(), 'line_id',
            BillLine::tableName(), 'pk','SET NULL', 'SET NULL');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(InvoiceLine::tableName(), 'line_id');
    }
}
