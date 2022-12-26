<?php

use app\models\PaymentApiInfo;

/**
 * Class m221226_150149_payment_api_check
 */
class m221226_150149_payment_api_check extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(PaymentApiInfo::tableName(), 'channel', $this->string(255));
        $this->addColumn(PaymentApiInfo::tableName(), 'payment_no', $this->string(32));

        $this->execute("
            UPDATE newpayment_api_info i, newpayments p
            SET i.channel = p.ecash_operator,
                i.payment_no = p.payment_no
            WHERE p.id = i.payment_id
        ");

        $this->createIndex('unq-'. PaymentApiInfo::tableName(), PaymentApiInfo::tableName(), ['channel', 'payment_no'], true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(PaymentApiInfo::tableName(), 'channel');
        $this->dropColumn(PaymentApiInfo::tableName(), 'payment_no');
    }
}
