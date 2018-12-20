<?php

use app\models\ClientAccount;
use app\models\ClientBlockedComment;

/**
 * Class m181211_102301_add_client_blocked_comment_table
 */
class m181211_102301_add_client_blocked_comment_table extends \app\classes\Migration
{
    private $tableName;
    private $clientTableName;

    public function init()
    {
        parent::init();
        $this->tableName = ClientBlockedComment::tableName();
        $this->clientTableName = ClientAccount::tableName();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->notNull(),
            'comment' => $this->text(),
            'created_at' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey("fk-{$this->tableName}-{$this->clientTableName}",
            $this->tableName,
            'account_id',
            $this->clientTableName,
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("fk-{$this->tableName}-{$this->clientTableName}", $this->tableName);
        $this->dropTable($this->tableName);
    }
}
