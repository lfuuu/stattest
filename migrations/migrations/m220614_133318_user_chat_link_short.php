<?php

/**
 * Class m220614_133318_user_chat_link_short
 */
class m220614_133318_user_chat_link_short extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\User::tableName(),  "rocket_nick", $this->string(64));

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\User::tableName(),  "rocket_nick", $this->string(20));
    }
}
