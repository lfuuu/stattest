<?php

use app\models\Bill;
use app\models\PaymentOrder;

/**
 * Class m180416_092106_add_column_to_newbills
 */
class m180416_092106_add_column_to_newbills extends \app\classes\Migration
{
    private $_addColumn = 'payment_date';
    private $_alterColumn = 'payment_id';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Bill::tableName(), $this->_addColumn, $this->date());

        $paymentOrder = PaymentOrder::tableName();
        PaymentOrder::getDb()
            ->createCommand("
                DELETE newpayments_orders.* FROM newpayments_orders WHERE payment_id LIKE '%/%' OR payment_id LIKE '%-%';
            ")
            ->execute();
        $this->dropPrimaryKey('', $paymentOrder);
        $this->dropIndex($this->_alterColumn, $paymentOrder);
        $this->alterColumn($paymentOrder, $this->_alterColumn, $this->integer());
        $this->addPrimaryKey('', $paymentOrder, ['client_id', 'bill_no', 'payment_id']);
        $this->createIndex($this->_alterColumn, $paymentOrder, $this->_alterColumn);

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Bill::tableName(), $this->_addColumn);

        $paymentOrder = PaymentOrder::tableName();
        $this->dropPrimaryKey('', $paymentOrder);
        $this->dropIndex($this->_alterColumn, $paymentOrder);
        $this->alterColumn($paymentOrder, $this->_alterColumn, $this->string(32));
        $this->addPrimaryKey('', $paymentOrder, ['client_id', 'bill_no', 'payment_id']);
        $this->createIndex($this->_alterColumn, $paymentOrder, $this->_alterColumn);
    }
}
