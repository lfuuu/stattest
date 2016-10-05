<?php

use app\models\EventQueue;

class m161005_110909_alter_event_queue_param extends \app\classes\Migration
{
    public function up()
    {
        $tableName = EventQueue::tableName();
        $this->alterColumn($tableName, 'param', $this->text());
    }

    public function down()
    {
        $tableName = EventQueue::tableName();
        $this->alterColumn($tableName, 'param', $this->string(255));
    }
}