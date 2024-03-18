<?php

/**
 * Class m240315_104041_payment_info_short
 */
class m240315_104041_payment_info_short extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\PaymentInfo::tableName(), 'payer', $this->string(256));
        $this->addColumn(\app\models\PaymentInfo::tableName(), 'getter', $this->string(256));
        $this->addColumn(\app\models\PaymentInfo::tableName(), 'comment', $this->string(256));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\PaymentInfo::tableName(), 'payer');
        $this->dropColumn(\app\models\PaymentInfo::tableName(), 'getter');
        $this->dropColumn(\app\models\PaymentInfo::tableName(), 'comment');
    }
}
