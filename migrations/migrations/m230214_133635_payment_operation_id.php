<?php

use app\classes\Migration;
use app\models\PaymentApiInfo;
use yii\db\Expression;

/**
 * Class m230214_133635_payment_operation_id
 */
class m230214_133635_payment_operation_id extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(PaymentApiInfo::tableName(), 'operation_id', $this->string(32));
        $this->update(PaymentApiInfo::tableName(), ['operation_id' => new Expression('payment_no')]);
        $this->alterColumn(PaymentApiInfo::tableName(), 'operation_id', $this->string(32)->notNull()->after('payment_no'));
        $this->dropIndex('unq-newpayment_api_info', PaymentApiInfo::tableName());
        $this->createIndex('unq-newpayment_api_info', PaymentApiInfo::tableName(), ['channel', 'operation_id'], true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('unq-newpayment_api_info', PaymentApiInfo::tableName());
        $this->createIndex('unq-newpayment_api_info', PaymentApiInfo::tableName(), ['channel', 'payment_no'], true);
        $this->dropColumn(PaymentApiInfo::tableName(), 'operation_id');
    }
}