<?php

/**
 * Class m170418_092904_notifier_log_remove
 */
class m170418_092904_notifier_log_remove extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->dropTable('notifier_log');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->createTable('notifier_log', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11),
            'action' => $this->string(100),
            'value' => $this->string(100),
            'created_at' => $this->dateTime(),
            'result' => $this->string(100),
            'updated_at' => $this->dateTime(),
        ], 'ENGINE=InnoDB CHARSET=utf8');
    }
}
