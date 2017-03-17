<?php
use app\models\EventQueueIndicator;

/**
 * Class m170317_100419_event_queue_indicator_section
 */
class m170317_100419_event_queue_indicator_section extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(EventQueueIndicator::tableName(), 'section', $this->string());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(EventQueueIndicator::tableName(), 'section');
    }
}
