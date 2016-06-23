<?php

use app\models\ActualCallChat;

class m160622_142642_usage_call_chat_actual_chat_name extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(ActualCallChat::tableName(), 'comment', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn(ActualCallChat::tableName(), 'comment');
    }
}