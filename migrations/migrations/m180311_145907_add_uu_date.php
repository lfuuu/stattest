<?php

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;

/**
 * Class m180311_145907_add_uu_date
 */
class m180311_145907_add_uu_date extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'tariff_period_utc', $this->dateTime());

        // установить дату последней смены тарифа
        $db = Yii::$app->db;
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $sql = <<<SQL
            UPDATE
                {$accountTariffTableName} account_tariff
            SET
                account_tariff.tariff_period_utc = (
                    SELECT
                        account_tariff_log.actual_from_utc
                    FROM
                        {$accountTariffLogTableName} account_tariff_log
                    WHERE
                        account_tariff.id = account_tariff_log.account_tariff_id
                        AND account_tariff_log.actual_from_utc <= :now
                    ORDER BY
                        account_tariff_log.actual_from_utc DESC,
                        account_tariff_log.id DESC
                    LIMIT 1
                ) 
SQL;
        $db->createCommand($sql,
            [
                ':now' => DateTimeZoneHelper::getUtcDateTime()
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ])
            ->execute();
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'tariff_period_utc');
    }
}
