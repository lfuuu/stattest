<?php

use app\classes\Migration;
use app\models\ClientStructureChangeRegistry;

/**
 * Class m231027_210454_client_change_register
 */
class m231027_210454_client_change_register extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = ClientStructureChangeRegistry::tableName();
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'section' => $this->string(255)->notNull(),
            'model_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uix-'.$table, $table, ['section', 'model_id'], true);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(ClientStructureChangeRegistry::tableName());
    }
}
