<?php
use app\models\HistoryChanges;

/**
 * Class m170410_104437_convert_uu_history
 */
class m170410_104437_convert_uu_history extends \app\classes\Migration
{
    /**
     * Up
     *
     * Не надо меня спрашивать, зачем 8 (восемь!) слэшей вместо одного!
     */
    public function safeUp()
    {
        $historyChangesTableName = HistoryChanges::tableName();
        $sql = <<<SQL
            UPDATE {$historyChangesTableName}
            SET model = REPLACE(model, 'app\\\\classes\\\\uu\\\\model', 'app\\\\modules\\\\uu\\\\models')
            WHERE model LIKE 'app\\\\\\\\classes\\\\\\\\uu\\\\\\\\model%'
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $historyChangesTableName = HistoryChanges::tableName();
        $sql = <<<SQL
            UPDATE {$historyChangesTableName}
            SET model = REPLACE(model, 'app\\\\modules\\\\uu\\\\models', 'app\\\\classes\\\\uu\\\\model')
            WHERE model LIKE 'app\\\\\\\\modules\\\\\\\\uu\\\\\\\\models%'
SQL;
        $this->execute($sql);
    }
}
