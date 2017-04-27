<?php
use app\modules\uu\models\AccountTariffResourceLog;

/**
 * Class m170424_163528_add_resource_sync_time
 */
class m170424_163528_add_resource_sync_time extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariffResourceLog::tableName(), 'sync_time', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariffResourceLog::tableName(), 'sync_time');
    }
}
