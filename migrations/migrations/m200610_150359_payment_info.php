<?php

use app\classes\Migration;
use app\models\PaymentInfo;
use app\models\Payment;

/**
 * Class m200610_150359_payment_info
 */
class m200610_150359_payment_info extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {

        $stringType = $this->string(128)->notNull()->defaultValue('');
        $this->createTable(PaymentInfo::tableName(), [
            'payment_id' => $this->integer()->unsigned()->notNull(),
            'payer_inn' => $stringType,
            'payer_bik' => $stringType,
            'payer_bank' => $stringType,
            'payer_account' => $stringType,
            'getter_inn' => $stringType,
            'getter_bik' => $stringType,
            'getter_bank' => $stringType,
            'getter_account' => $stringType,
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('pk-payment_info', PaymentInfo::tableName(), 'payment_id');

        $this->addForeignKey('fk-payment_info', PaymentInfo::tableName(), 'payment_id', Payment::tableName(), 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('pk-client_inn-inn', \app\models\ClientInn::tableName(), 'inn');
        $this->createIndex('idx-clients-inn', \app\models\ClientAccount::tableName(), 'pay_acc');

        $this->createIndex('idx-newpayments-oper_date-payment_no', Payment::tableName(), ['oper_date', 'payment_no']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PaymentInfo::tableName());
        $this->dropIndex('pk-client_inn-inn', \app\models\ClientInn::tableName());
        $this->dropIndex('idx-clients-inn', \app\models\ClientAccount::tableName());
        $this->dropIndex('idx-newpayments-oper_date-payment_no', Payment::tableName());
    }
}
