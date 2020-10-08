<?php

use app\classes\Migration;
use app\models\danycom\PhoneHistory;

/**
 * Class m201008_132444_dc_number_info
 */
class m201008_132444_dc_number_info extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PhoneHistory::tableName(), [
            'id' => $this->primaryKey(),
            'process_id' => $this->string(16),
            'date_request' => $this->string(32),
            'phone_contact' => $this->string(16),
            'number' => $this->tinyInteger(4),
            'phone_ported' => $this->string(16),
            'process_type' => $this->string(255),
            'from' => $this->string(255),
            'to' => $this->string(255),
            'state' => $this->string(255),
            'state_current' => $this->string(255),
            'region' => $this->string(255),
            'date_ported' => $this->string(32),
            'last_message' => $this->string(255),
            'date_sent' => $this->string(32),
            'last_sender' => $this->string(255),
            'code' => $this->integer(11)
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx-phone_contact-number', PhoneHistory::tableName(), ['phone_contact', 'number']);

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PhoneHistory::tableName());
    }
}
