<?php

use app\classes\uu\model\ServiceType;

class m160829_164133_add_uu_call_chat extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ServiceType::tableName();
        $this->insert($tableName, [
            'id' => ServiceType::ID_CALL_CHAT,
            'name' => 'Звонок-чат',
        ]);
    }

    public function down()
    {
        $tableName = ServiceType::tableName();
        $this->delete($tableName, [
            'id' => ServiceType::ID_CALL_CHAT,
        ]);
    }
}