<?php

use app\models\EventQueue;
use yii\db\Expression;

/**
 * Class m171203_143803_alter_event_queue
 */
class m171203_143803_alter_event_queue extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->dropColumn(EventQueue::tableName(), 'date');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->addColumn(EventQueue::tableName(), 'date', $this->dateTime());
        EventQueue::updateAll(['date' => new Expression('insert_time')]);
    }
}
