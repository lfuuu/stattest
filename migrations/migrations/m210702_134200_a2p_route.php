<?php

use app\classes\Migration;
use app\models\dictionary\A2pRoute;

/**
 * Class m210702_134200_a2p_route
 */
class m210702_134200_a2p_route extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        /*
        $this->createTable(A2pRoute::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string(64),
        ]);

        $this->insert(A2pRoute::tableName(), ['id' => 1, 'name' => 'mcnid']);
        $this->insert(A2pRoute::tableName(), ['id' => 2, 'name' => 'tele2id']);
        $this->insert(A2pRoute::tableName(), ['id' => 3, 'name' => 'devinoid']);
        $this->insert(A2pRoute::tableName(), ['id' => 4, 'name' => 'API_Kannel']);
        */
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // $this->dropTable(A2pRoute::tableName());
    }
}
