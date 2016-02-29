<?php

class m160226_122758_actual_call_chat extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable('actual_call_chat', [
            'client_id' => 'int(11) NOT NULL',
            'usage_id' => 'int(11) NOT NULL',
            'tarif_id' => 'int(11) NOT NULL'
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex("client_id__usage_id", "actual_call_chat", ['client_id','usage_id'], true);
    }

    public function down()
    {
        echo "m160226_122758_actual_call_chat cannot be reverted.\n";

        $this->dropTable('actual_call_chat');

        return false;
    }
}