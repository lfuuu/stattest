<?php

use app\models\DeferredTask;
use app\models\User;

/**
 * Class m190121_114009_add_deferred_task_table
 */
class m190121_114009_add_deferred_task_table extends \app\classes\Migration
{
    private $tableName;
    private $userTableName;

    public function init()
    {
        parent::init();
        $this->tableName = DeferredTask::tableName();
        $this->userTableName = User::tableName();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'filename' => $this->string(),
            'params' => $this->text(),
            'tmp_files' => $this->text(),
            'filter_model' => $this->string(),
            'status' => $this->tinyInteger()->defaultValue(0),
            'status_text' => $this->string(),
            'created_at' => $this->dateTime(),
            'downloaded_at' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey(
            "fk-{$this->tableName}-{$this->userTableName}",
            $this->tableName,
            'user_id',
            $this->userTableName,
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-{$this->tableName}-{$this->userTableName}", $this->tableName);
        $this->dropTable($this->tableName);
    }
}
