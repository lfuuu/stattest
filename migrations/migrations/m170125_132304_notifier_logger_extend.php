<?php

use \app\modules\notifier\models\Logger;

/**
 * Class m170125_132304_notifier_logger_extend
 */
class m170125_132304_notifier_logger_extend extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Logger::tableName();

        $this->addColumn($tableName, 'result', $this->string(100));
        $this->addColumn($tableName, 'updated_at', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Logger::tableName();

        $this->dropColumn($tableName, 'result');
        $this->dropColumn($tableName, 'updated_at');
    }

}
