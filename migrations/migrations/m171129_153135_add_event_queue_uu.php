<?php

use app\models\EventQueue;

/**
 * Class m171129_153135_add_event_queue_uu
 */
class m171129_153135_add_event_queue_uu extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = EventQueue::tableName();
        $this->addColumn($tableName, 'account_tariff_id', $this->integer());
        $this->createIndex('account_tariff_id', $tableName, 'account_tariff_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = EventQueue::tableName();
        // $this->dropIndex('account_tariff_id', $tableName); // сам удалится при удалении поля
        $this->dropColumn($tableName, 'account_tariff_id');
    }
}
