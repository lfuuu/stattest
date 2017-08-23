<?php
use app\classes\Event;
use app\models\EventQueue;

/**
 * Class m170822_130525_create_core_admin_to_owner
 */
class m170822_130525_create_core_admin_to_owner extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(
            EventQueue::tableName(),
            ['event' => Event::CORE_CREATE_OWNER],
            ['event' => 'core_create_admin']
        );


        $this->update(
            EventQueue::tableName(),
            ['event' => Event::CHECK_CREATE_CORE_OWNER],
            ['event' => 'check_create_core_admin']
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(
            EventQueue::tableName(),
            ['event' => 'core_create_admin'],
            ['event' => Event::CORE_CREATE_OWNER]
        );

        $this->update(
            EventQueue::tableName(),
            ['event' => 'check_create_core_admin'],
            ['event' => Event::CHECK_CREATE_CORE_OWNER]
        );
    }
}
