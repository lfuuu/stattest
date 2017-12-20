<?php

use app\models\Payment;
use app\modules\payments\models\PaymentStripe;

/**
 * Class m171214_091720_payment_stripe
 */
class m171214_091720_payment_stripe extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PaymentStripe::tableName(), [
            'payment_id' => $this->integer(11)->unsigned()->notNull(),
            'token_id' => $this->string()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'sum' => $this->decimal(12, 2)->notNull(),
            'currency' => $this->char(3)->notNull(),
            'token_data' => $this->text(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->alterColumn(Payment::tableName(), 'ecash_operator', "enum('" . Payment::ECASH_CYBERPLAT . "','" . Payment::ECASH_PAYPAL . "','" . Payment::ECASH_YANDEX . "','" . Payment::ECASH_SBERBANK . "', '" . Payment::ECASH_QIWI . "', '" . Payment::ECASH_STRIPE . "') DEFAULT NULL");
        $this->createIndex('uidx-paymentt_id', PaymentStripe::tableName(), 'payment_id', true);
        $this->addForeignKey('fk-payments-id', PaymentStripe::tableName(), 'payment_id', Payment::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(Payment::tableName(), 'ecash_operator', "enum('" . Payment::ECASH_CYBERPLAT . "','" . Payment::ECASH_PAYPAL . "','" . Payment::ECASH_YANDEX . "','" . Payment::ECASH_SBERBANK . "', '" . Payment::ECASH_QIWI . "') DEFAULT NULL");
        $this->dropTable(PaymentStripe::tableName());
    }
}
