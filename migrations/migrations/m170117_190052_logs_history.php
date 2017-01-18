<?php
use app\models\UserRight;

/**
 * Class m170117_190052_logs_history
 */
class m170117_190052_logs_history extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $right = UserRight::findOne(['resource' => 'logs']);
        if ($right) {
            $right->values = 'read,history_version,history_changes';
            $right->values_desc = 'Просмотр,Просмотри истории версий,Просмотр истории изменений';
            $right->save();
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $right = UserRight::findOne(['resource' => 'logs']);
        if ($right) {
            $right->values = 'read';
            $right->values_desc = 'просмотр';
            $right->save();
        }
    }
}
