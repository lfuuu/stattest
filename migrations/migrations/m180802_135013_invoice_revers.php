<?php

use app\models\Invoice;

/**
 * Class m180802_135013_invoice_revers
 */
class m180802_135013_invoice_revers extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'is_reversal', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'is_reversal');
    }
}
