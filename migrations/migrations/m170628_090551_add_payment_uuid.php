<?php
use app\models\Payment;
use app\models\PaymentAtol;

/**
 * Class m170628_090551_add_payment_uuid
 */
class m170628_090551_add_payment_uuid extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = PaymentAtol::tableName();
        $this->createTable($tableName, [
            'id' => $this->integer()->unsigned()->notNull(), // это PK, но не autoincrement
            'uuid' => $this->string(255)->notNull(),
            'uuid_status' => $this->integer()->notNull(),
            'uuid_log' => $this->text(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey('id', $tableName, 'id');
        $this->addForeignKey('fk-id', $tableName, 'id', Payment::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PaymentAtol::tableName());
    }
}
