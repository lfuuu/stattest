<?php

use app\models\Lead;

/**
 * Class m181010_132637_add_columns_to_lead
 */
class m181010_132637_add_columns_to_lead extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = Lead::tableName();
        $this->addColumn($table, 'did', $this->string());
        $this->addColumn($table, 'did_mcn', $this->string());
        $this->createIndex('idx-'.$table.'-created_at', $table, 'created_at');
        $this->createIndex('idx-'.$table.'-did', $table, 'did');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $table = Lead::tableName();
        $this->dropColumn($table, 'did');
        $this->dropColumn($table, 'did_mcn');
        $this->dropIndex('idx-'.$table.'-created_at', $table);
        $this->dropIndex('idx-'.$table.'-did', $table);
    }
}
