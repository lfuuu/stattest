<?php

namespace app\commands\convert;

use app\exceptions\ModelValidationException;
use app\models\UsageTrunkSettings;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use yii\console\Controller;


class UsagesController extends Controller
{
    public function actionUsageTrunkSettingsLink()
    {
        $query = UsageTrunkSettings::find()->where(['usage_id' => 632166])->orderBy(['usage_id' => SORT_ASC, 'id' => SORT_ASC]);;

        /** @var UsageTrunkSettings $usage */
        foreach ($query->each() as $usage) {

            $data = $this->_getUsageData($usage->usage_id, $usage->package_id);

            if (!$data) {
                echo " -";
                continue;
            }

            echo " +";

            $usage->activation_dt = $data[1];
            if ($data[2]) {
                $usage->expire_dt = $data[2];
            }

            $usage->account_package_id = $data[0];

            if (!$usage->save()) {
                throw new ModelValidationException($usage);
            }
        }
    }

    private function _getUsageData($usageId, $tariffId)
    {
        static $storage = [];
        static $prevUsageId = null;

        if (!$tariffId) {
            return false;
        }

        if ($prevUsageId != $usageId) {
            $storage = $this->_loadUsage($usageId);
            $prevUsageId = $usageId;
        }

        if (!$storage || !isset($storage[$tariffId]) || !count($storage[$tariffId])) {
            return false;
        }

        return array_shift($storage[$tariffId]);
    }

    private function _loadUsage($usageId)
    {
        $accountTariffQuery = AccountTariff::find()->where([
            'prev_account_tariff_id' => $usageId
        ]);

        $data = [];
        /** @var AccountTariff $accountTariff */
        foreach ($accountTariffQuery->each() as $accountTariff) {
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            /** @var AccountTariffLog $accountTariffLog */
            ksort($accountTariffLogs);
            $accountTariffLog = reset($accountTariffLogs);

            $accountTariffLogOffs = array_filter($accountTariffLogs, function ($log) {
                /** @var AccountTariffLog $log */
                return !$log->tariff_period_id;
            });

            krsort($accountTariffLogOffs);

            $accountTariffLogOff = null;
            if ($accountTariffLogOffs) {
                $accountTariffLogOff = reset($accountTariffLogOffs);
            }

            if (!isset($data[$accountTariffLog->tariffPeriod->tariff_id])) {
                $data[$accountTariffLog->tariffPeriod->tariff_id] = [];
            }
            $data[$accountTariffLog->tariffPeriod->tariff_id][] = [$accountTariff->id, $accountTariffLog->actual_from_utc, $accountTariffLogOff ? $accountTariffLogOff->actual_from_utc : null];
        }

        return $data;
    }
}

