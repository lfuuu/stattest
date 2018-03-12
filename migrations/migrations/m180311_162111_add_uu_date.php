<?php

use app\models\ClientAccount;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;

/**
 * Class m180311_162111_add_uu_date
 */
class m180311_162111_add_uu_date extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'account_log_period_utc', $this->dateTime());

        // установить дату "Абонентка списана до"
        // date_to получается чуть меньше (время 00:00 вместо 23:59). Поэтому добавляем 1 день и вычитаем таймзону
        $db = Yii::$app->db;
        $clientAccountTableName = ClientAccount::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $sql = <<<SQL
            UPDATE
                {$accountTariffTableName} account_tariff,
                {$clientAccountTableName} client
            SET
                account_tariff.account_log_period_utc = (
                    SELECT
                        convert_tz(
                            DATE_ADD(
                                MAX(account_log_period.date_to), 
                                INTERVAL 1 DAY
                            ),
                            client.timezone_name,
                            'UTC' 
                        )
                    FROM
                        {$accountLogPeriodTableName} account_log_period
                    WHERE
                        account_tariff.id = account_log_period.account_tariff_id
                ) 
            WHERE
                account_tariff.client_account_id = client.id
SQL;
        $db->createCommand($sql)->execute();
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'account_log_period_utc');
    }
}
