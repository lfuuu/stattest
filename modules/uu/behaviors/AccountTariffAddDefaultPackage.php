<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\Module;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Exception;


class AccountTariffAddDefaultPackage extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'addDefaultOrBundlePackages',
        ];
    }

    /**
     *
     * @param Event $event
     * @throws ModelValidationException
     * @throws Exception
     */
    public function addDefaultOrBundlePackages(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;

        // only activation or tariff change
        if (!$accountTariffLog->tariff_period_id) {
            return true;
        }

        $accountTariff = $accountTariffLog->accountTariff;

        if (!in_array($accountTariff->service_type_id, ServiceType::$packages)) {
            return true;
        }

        if (
            !$accountTariffLog->tariffPeriod
            || !$accountTariffLog->tariffPeriod->tariff
        ) {
            Yii::error('Bad tariff: ' . var_export(__FILE__ . '::' . __LINE__ . ': atl_id: ' . $accountTariffLog->id . '/at_id: ' . $accountTariff->id . ', tariff_peirof_id: ' . $accountTariffLog->tariff_period_id, true));
            return false;
        }
        $tariff = $accountTariffLog->tariffPeriod->tariff;

        if (!($tariff->is_default || $tariff->is_bundle)) {
            return true;
        }

        $accountTariff->refresh();
        $accountTariffLogs = $accountTariff->accountTariffLogs;
        reset($accountTariffLogs);
        /** @var AccountTariffLog $accountTariffPrevLog */
        $accountTariffPrevLog = next($accountTariffLogs);

        $data = [
            'client_account_id' => $accountTariff->client_account_id,
            'account_tariff_id' => $accountTariff->id,
            'old_tariff_period_id' => $accountTariffPrevLog ? $accountTariffPrevLog->tariff_period_id : null,
            'new_tariff_period_id' => $accountTariffLog->tariff_period_id,
            'account_tariff_log_id' => $accountTariffLog->id,
        ];

        if ($tariff->is_bundle) {
            EventQueue::go(Module::EVENT_VOIP_BUNDLE, $data);
        } elseif ($tariff->is_default) {
            EventQueue::go(Module::EVENT_ADD_DEFAULT_PACKAGES, $data);
        }

        return true;
    }
}
