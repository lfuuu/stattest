<?php

use app\models\HistoryChanges;
use app\models\HistoryVersion;

/**
 * Class m171102_115035_alter_history
 */
class m171102_115035_alter_history extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(HistoryVersion::tableName(), 'model_id', $this->bigInteger());
        $this->alterColumn(HistoryChanges::tableName(), 'model_id', $this->bigInteger());
        $this->alterColumn(HistoryChanges::tableName(), 'parent_model_id', $this->bigInteger());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(HistoryVersion::tableName(), 'model_id', $this->integer());
        $this->alterColumn(HistoryChanges::tableName(), 'model_id', $this->integer());
        $this->alterColumn(HistoryChanges::tableName(), 'parent_model_id', $this->integer());
    }
}
