<?php

/**
 * Class m230523_113648_event_queue_param_long
 */
class m230523_113648_event_queue_param_long extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\EventQueue::tableName(), 'param', 'LONGTEXT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\EventQueue::tableName(), 'param', $this->text());
    }
}
