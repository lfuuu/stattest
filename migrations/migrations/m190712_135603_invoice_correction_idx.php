<?php

use app\models\Invoice;

/**
 * Class m190712_135603_invoice_correction_idx
 */
class m190712_135603_invoice_correction_idx extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'correction_idx', $this->smallInteger());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'correction_idx');
    }
}
