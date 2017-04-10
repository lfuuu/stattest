<?php
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\exceptions\ModelValidationException;

/**
 * Class m170403_091738_convert_tariff_resource
 */
class m170403_091738_convert_tariff_resource extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $query = AccountTariffLog::find()
            ->where(['IS NOT', 'tariff_period_id', null])
            ->orderBy(['actual_from_utc' => SORT_ASC]);
        /** @var AccountTariffLog $accountTariffLog */
        foreach ($query->each() as $accountTariffLog) {

            $tariff = $accountTariffLog->tariffPeriod->tariff;
            $tariffResources = $tariff->tariffResources;
            foreach ($tariffResources as $tariffResource) {

                $accountTariffResourceLog = new AccountTariffResourceLog;
                $accountTariffResourceLog->account_tariff_id = $accountTariffLog->account_tariff_id;
                $accountTariffResourceLog->actual_from_utc = $accountTariffLog->actual_from_utc;
                $accountTariffResourceLog->insert_time = $accountTariffLog->actual_from_utc; // задним числом
                $accountTariffResourceLog->insert_user_id = $accountTariffLog->insert_user_id;
                $accountTariffResourceLog->resource_id = $tariffResource->resource_id;
                $accountTariffResourceLog->amount = $tariffResource->amount;
                if (!$accountTariffResourceLog->save($runValidation = false)) { // валидация не даст добавить задним числом
                    throw new ModelValidationException($accountTariffResourceLog);
                }
            }
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        AccountTariffResourceLog::deleteAll();
    }
}
