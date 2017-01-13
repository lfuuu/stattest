<?php
use app\models\HistoryChanges;
use app\models\HistoryVersion;
use yii\db\Expression;

/**
 * Class m161229_100209_update_history_model */
class m161229_100209_update_history_model extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $historyVersionTableName = HistoryVersion::tableName();
        $this->update($historyVersionTableName, ['model' => new Expression('CONCAT("app\\\\models\\\\", REPLACE(model, "_", "\\\\"))')]);

        $historyChangesTableName = HistoryChanges::tableName();
        $this->update($historyChangesTableName, ['model' => new Expression('CONCAT("app\\\\models\\\\", REPLACE(model, "_", "\\\\"))')]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $historyVersionTableName = HistoryVersion::tableName();
        $this->update($historyVersionTableName, ['model' => new Expression('REPLACE(REPLACE(model, "app\\\\models\\\\", ""), "_", "\\\\")')]);

        $historyChangesTableName = HistoryChanges::tableName();
        $this->update($historyChangesTableName, ['model' => new Expression('REPLACE(REPLACE(model, "app\\\\models\\\\", ""), "_", "\\\\")')]);
    }
}
