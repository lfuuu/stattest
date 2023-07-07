<?php

/**
 * Class m230707_145106_payment_no_resize
 */
class m230707_145106_payment_no_resize extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // payment_no   varchar(32) default '0' not null,
        $this->alterColumn(\app\models\Payment::tableName(), 'payment_no', $this->string(255)->notNull()->defaultValue('0'));

        // payment_no   varchar(32) null,
        $this->alterColumn(\app\models\PaymentApiInfo::tableName(), 'payment_no', $this->string(255));

        // operation_id varchar(32) not null,
        $this->alterColumn(\app\models\PaymentApiInfo::tableName(), 'operation_id', $this->string(255)->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
