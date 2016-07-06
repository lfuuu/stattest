<?php

use app\models\PaymentSberOnline;

class m160704_143742_payments_sber_online extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable(PaymentSberOnline::tableName(), [
            'id' => $this->primaryKey(),
            'payment_sent_date' => $this->date(),
            'payment_received_date' => $this->date(),
            'code1' => $this->string(32),
            'code2' => $this->string(32),
            'code3' => $this->string(32),
            'code4' => $this->string(32),
            'code5' => $this->string(32),
            'payer' => $this->string(255),
            'description' => $this->string(255),
            'sum_paid' => $this->decimal(12,2),
            'sum_received' => $this->decimal(12,2),
            'sum_fee' => $this->decimal(12,2),
            'day' => $this->integer(4),
            'month' => $this->integer(4),
            'year' => $this->integer(4),
            'created_at' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('payment_sent_date__code123', PaymentSberOnline::tableName(), ['payment_sent_date', 'code1', 'code2','code3'], true);
        $this->createIndex('payment_date', PaymentSberOnline::tableName(), ['year', 'month', 'day']);
    }

    public function down()
    {
        $this->dropTable(PaymentSberOnline::tableName());
    }
}