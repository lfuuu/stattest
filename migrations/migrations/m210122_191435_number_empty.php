<?php

/**
 * Class m210122_191435_number_empty
 */
class m210122_191435_number_empty extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable('voip_service_empty', [
            'id' => $this->primaryKey(),
            'number' => $this->bigInteger(),
            'client_id' => $this->integer(),
            'activation_dt' => $this->dateTime(),
            'expire_dt' => $this->dateTime()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable('voip_service_empty');
    }
}
