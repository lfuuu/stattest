<?php
use app\classes\uu\model\AccountTariffLog;

/**
 * Class m170113_124054_alter_uu_account_tariff_log
 */
class m170113_124054_alter_uu_account_tariff_log extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $accountTariffLog = AccountTariffLog::tableName();
        $this->update($accountTariffLog, ['actual_from_utc' => new \yii\db\Expression('DATE_FORMAT(insert_time, "%Y-%m-%d 21:00:00")')], ['actual_from_utc' => null]);
        $this->alterColumn($accountTariffLog, 'actual_from_utc', $this->dateTime()->notNull());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $accountTariffLog = AccountTariffLog::tableName();
        $this->alterColumn($accountTariffLog, 'actual_from_utc', $this->dateTime());
    }
}
