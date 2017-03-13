<?php
use app\models\important_events\ImportantEvents;

/**
 * Class m170310_131957_index_important_events
 */
class m170310_131957_index_important_events extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createIndex('idx-client_id-event', ImportantEvents::tableName(), ['client_id', 'event']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('idx-client_id-event', ImportantEvents::tableName());
    }
}
