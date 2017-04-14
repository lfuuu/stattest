<?php
use app\models\Payment;
use app\models\SberbankOrder;

/**
 * Class m161230_131400_sber_bank_order
 */
class m161230_131400_sber_bank_order extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(SberbankOrder::tableName(), [
            'created_at' => $this->dateTime()->notNull(),
            'order_id' => $this->string()->notNull(),
            'bill_no' => $this->string()->notNull(),
            'payment_id' => $this->integer(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'order_url' => $this->string()->notNull()->defaultValue(''),
            'info_json' => $this->string(4096)
        ]);

        $this->createIndex('idx-order_id', SberbankOrder::tableName(), 'order_id');
        $this->createIndex('uidx-bill_no', SberbankOrder::tableName(), 'bill_no', true);

        $this->alterColumn(Payment::tableName(), 'ecash_operator', "enum('cyberplat','paypal','yandex', '". Payment::ECASH_SBERBANK."') DEFAULT NULL");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(SberbankOrder::tableName());
        $this->alterColumn(Payment::tableName(), 'ecash_operator', "enum('cyberplat','paypal','yandex') DEFAULT NULL");
    }
}
