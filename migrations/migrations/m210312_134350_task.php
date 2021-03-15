<?php

use app\classes\Migration;
use app\models\Task;

/**
 * Class m210312_134350_task
 */
class m210312_134350_task extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Task::tableName(), [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime()->notNull(),
            'filter_class' => $this->string(),
            'filter_data_json' => $this->text(),
            'params_json' => $this->text(),
            'status' => $this->string(),
            'count_all' => $this->integer(),
            'count_done' => $this->integer(),
            'progress' => 'LONGTEXT',
        ]);

        $this->createIndex('idx-status', Task::tableName(), ['status']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Task::tableName());
    }
}
