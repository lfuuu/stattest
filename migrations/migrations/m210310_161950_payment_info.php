<?php

use app\classes\Migration;
use app\models\PaymentApiInfo;
use app\models\Payment;

/**
 * Class m210310_161950_payment_info
 */
class m210310_161950_payment_info extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PaymentApiInfo::tableName(), [
            'payment_id' => 'int(11) unsigned auto_increment primary key',
            'created_at' => $this->dateTime()->notNull(),
            'info_json' => $this->text(),
            'request' => $this->text(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('fk-' . PaymentApiInfo::tableName() . '-payment_id',
            PaymentApiInfo::tableName(), 'payment_id',
            Payment::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PaymentApiInfo::tableName());
    }
}
