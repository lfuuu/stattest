<?php
use app\models\EventQueue;
use app\models\EventQueueIndicator;


/**
 * Handles the creation for table `event_queue_indicator`.
 */
class m170303_122513_create_event_queue_indicator extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable(EventQueueIndicator::tableName(), [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'object' => $this->string()->notNull(),
            'object_id' => $this->integer()->notNull(),
            'event_queue_id' => $this->bigInteger(20)
        ]);

        $this->createIndex('idx-object-object_id', EventQueueIndicator::tableName(), ['object', 'object_id']);
        $this->addForeignKey(
            'fk-'. EventQueue::tableName().'-id',
            EventQueueIndicator::tableName(),
            'event_queue_id',
            EventQueue::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn(EventQueue::tableName(), 'trace', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(EventQueueIndicator::tableName());
        $this->dropColumn(EventQueue::tableName(), 'trace');
    }
}
