<?php

use app\models\EventQueue;

class m160923_132327_add_event_queue_insert_time extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(EventQueue::tableName(), 'insert_time', $this->dateTime());
    }

    public function down()
    {
        $this->dropColumn(EventQueue::tableName(), 'insert_time');
    }
}