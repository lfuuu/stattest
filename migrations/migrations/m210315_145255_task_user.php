<?php

use app\classes\Migration;
use app\models\Task;

/**
 * Class m210315_145255_task_user
 */
class m210315_145255_task_user extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Task::tableName(), 'user_id', $this->integer()->notNull()->defaultValue(\app\models\User::SYSTEM_USER_ID));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Task::tableName(), 'user_id');
    }
}
