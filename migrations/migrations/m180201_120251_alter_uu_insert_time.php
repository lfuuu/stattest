<?php

use app\modules\uu\models\AccountTariffResourceLog;

/**
 * Class m180201_120251_alter_uu_insert_time
 */
class m180201_120251_alter_uu_insert_time extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(AccountTariffResourceLog::tableName(), 'insert_time', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(AccountTariffResourceLog::tableName(), 'insert_time', $this->timestamp());
    }
}
