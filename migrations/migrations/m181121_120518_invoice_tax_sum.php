<?php

use app\models\Invoice;

/**
 * Class m181121_120518_invoice_tax_sum
 */
class m181121_120518_invoice_tax_sum extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'sum_without_tax', $this->decimal(12,2));
        $this->addColumn(Invoice::tableName(), 'sum_tax', $this->decimal(12,2));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'sum_without_tax');
        $this->dropColumn(Invoice::tableName(), 'sum_tax');
    }
}
