<?php

use app\models\ActualCallChat;
use app\models\UsageCallChat;

class m160628_111705_callchat_without_name extends \app\classes\Migration
{
    public function up()
    {
        $this->dropColumn(UsageCallChat::tableName(), 'comment');
        $this->dropColumn(ActualCallChat::tableName(), 'comment');
    }

    public function down()
    {
        $this->addColumn(UsageCallChat::tableName(), 'comment', $this->string(255)->defaultValue(null));
        $this->addColumn(ActualCallChat::tableName(), 'comment', $this->string(255)->defaultValue(null));
    }
}