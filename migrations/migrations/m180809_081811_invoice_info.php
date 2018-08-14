<?php

use app\models\Invoice;

/**
 * Class m180809_081811_invoice_info
 */
class m180809_081811_invoice_info extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'add_date', $this->dateTime()->notNull());
        $this->addColumn(Invoice::tableName(), 'reversal_date', $this->dateTime());
        $this->update(Invoice::tableName(), ['add_date' => '2018-08-06 12:00:00']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'add_date');
        $this->dropColumn(Invoice::tableName(), 'reversal_date');
    }
}