<?php

use app\classes\Migration;
use app\models\Invoice;

/**
 * Class m240809_103657_invoice_is_pay
 */
class m240809_103657_invoice_is_pay extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'is_payed', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn(Invoice::tableName(), 'payment_date', $this->date());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tableName(), 'is_payed');
        $this->dropColumn(Invoice::tableName(), 'payment_date');
    }
}
