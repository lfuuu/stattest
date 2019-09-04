<?php

use app\models\Invoice;

/**
 * Class m190903_085042_invoce_type
 */
class m190903_085042_invoce_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'is_invoice', $this->tinyInteger()->notNull()->defaultValue(1));
        $this->addColumn(Invoice::tableName(), 'is_act', $this->tinyInteger()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'is_invoice');
        $this->dropColumn(Invoice::tableName(), 'is_act');
    }
}
