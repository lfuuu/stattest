<?php

use app\classes\Migration;
use app\models\PaymentInfoShort;

/**
 * Class m240528_135819_payment_info_short
 */
class m240528_135819_payment_info_short extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PaymentInfoShort::tableName(),[
            'payment_id' => $this->primaryKey(),
            'type' => $this->string(256)->notNull()->defaultValue(''),
            'comment' => $this->string(1024)->notNull()->defaultValue('')
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PaymentInfoShort::tableName());
    }
}
