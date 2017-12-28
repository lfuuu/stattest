<?php

use app\models\ClientAccount;
use app\models\ClientAccountComment;
use app\models\User;

/**
 * Class m171227_103009_client_comment
 */
class m171227_103009_client_comment extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(ClientAccountComment::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer()->notNull(),
            'comment' => $this->string(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()
        ]);

        $this->addForeignKey('fk-' . ClientAccountComment::tableName() . '-account_id-' . ClientAccount::tableName() . '-id', ClientAccountComment::tableName(), 'account_id', ClientAccount::tableName(), 'id');
        $this->addForeignKey('fk-' . ClientAccountComment::tableName() . '-user_id-' . User::tableName() . '-id', ClientAccountComment::tableName(), 'user_id', User::tableName(), 'id');
        $this->createIndex('idx-'  .ClientAccountComment::tableName().'-account_id', ClientAccountComment::tableName(), ['account_id', 'created_at']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(ClientAccountComment::tableName());
    }
}
