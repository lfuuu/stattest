<?php

use app\models\EventQueue;
use app\models\EventQueueIndicator;

/**
 * Class m180112_105325_event_queue_indecator_constraint
 */
class m180112_105325_event_queue_indecator_constraint extends \app\classes\Migration
{
    private function _getFkName()
    {
        return 'fx-' . EventQueueIndicator::tableName() . '-event_queue_id-' . EventQueue::tableName() . '-id';
    }

    /**
     * Up
     */
    public function safeUp()
    {
        // нормальизуем целосность данных для создания ключа.
        // Данные без привязки не имеют ценности.

        $this->execute('
            DELETE i FROM
            `' . EventQueueIndicator::tableName() . '` i
            LEFT JOIN `' . EventQueue::tableName() . '` e ON (e.id = i.event_queue_id)
            WHERE e.id IS NULL
        ');

        $this->execute('ALTER TABLE `' . EventQueueIndicator::tableName() . '` ENGINE=InnoDB');
        $this->addForeignKey($this->_getFkName(),
            EventQueueIndicator::tableName(), 'event_queue_id',
            EventQueue::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->_getFkName(),
            EventQueueIndicator::tableName());
    }
}
