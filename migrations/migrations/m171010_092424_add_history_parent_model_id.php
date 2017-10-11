<?php

use app\models\HistoryChanges;

/**
 * Class m171010_092424_add_history_parent_model_id
 */
class m171010_092424_add_history_parent_model_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(HistoryChanges::tableName(), 'parent_model_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(HistoryChanges::tableName(), 'parent_model_id');
    }
}
