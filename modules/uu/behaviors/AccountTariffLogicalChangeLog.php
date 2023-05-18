<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffChange as Log;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use yii\base\Behavior;
use yii\base\Event;


class AccountTariffLogicalChangeLog extends Behavior
{
    static $transAccountData = [];

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\Exception
     */
    public function afterInsert(Event $event)
    {
        $sender = $event->sender;

        if ($event->sender instanceof AccountTariff) {
            self::$transAccountData[$event->sender->id] = 1;
        } elseif ($sender instanceof AccountTariffLog) {
            /** @var $sender AccountTariffLog */
            $accountTariff = $sender->accountTariff;
            unset(self::$transAccountData[$accountTariff->id]);
            $obj = $accountTariff->prev_account_tariff_id ? 'package' : 'service';
            $data = [
                    'account_tariff_id' => $accountTariff->prev_account_tariff_id ?: $accountTariff->id,
                    'tariff_period_id' => (int)$sender->tariff_period_id,
                    'actual_from_utc' => $sender->actual_from_utc,
                ] + ($accountTariff->prev_account_tariff_id ? ['package_id' => $accountTariff->id] : []);

            if (!$sender->tariff_period_id) {
                Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + ['action' => $obj . '_off']);
            } else {
                $logs = $accountTariff->accountTariffLogs;
                if (count($logs) > 1) {
                    reset($logs);
                    $fromTariffPeriodId = next($logs)->tariff_period_id;

                    Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + [
                            'action' => $obj . '_tariff_period_change',
                            'tariff_period_id_from' => (int)$fromTariffPeriodId,
                        ]);
                } else {
                    self::$transAccountData[$accountTariff->id] = 1;
                    Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + ['action' => $obj . '_on',]);
                }
            }
        } elseif ($sender instanceof AccountTariffResourceLog) {
            /** @var $sender AccountTariffResourceLog */
            if (isset(self::$transAccountData[$sender->account_tariff_id])) {
                // no need to log on add service
                return true;
            }

            /** $var $sender AccountTariffResourceLog */
            $accountTariff = $sender->accountTariff;

            if ($accountTariff->prev_account_tariff_id) {
                // no resource changes in package
                return true;
            }

            Log::add($accountTariff->client_account_id, $accountTariff->id, [
                'account_tariff_id' => $accountTariff->id,
                'resource_id' => $sender->resource_id,
                'amount' => (int)$sender->amount,
                'actual_from_utc' => $sender->actual_from_utc,
                'action' => 'resource_add'
            ]);

        }
        return true;
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\Exception
     */
    public function beforeDelete(Event $event)
    {
        $sender = $event->sender;

        if ($sender instanceof AccountTariffResourceLog) {
            /** $var $sender AccountTariffResourceLog */
            $accountTariff = $sender->accountTariff;

            Log::add($accountTariff->client_account_id, $accountTariff->id, [
                'account_tariff_id' => $accountTariff->id,
                'resource_id' => $sender->resource_id,
                'amount' => (int)$sender->amount,
                'actual_from_utc' => $sender->actual_from_utc,
                'action' => 'resource_del'
            ]);
        } elseif ($sender instanceof AccountTariffLog) {
            /** $var $sender AccountTariffLog */
            $accountTariff = $sender->accountTariff;

            $data = [
                    'account_tariff_id' => $accountTariff->prev_account_tariff_id ?: $accountTariff->id,
                    'tariff_period_id' => (int)$sender->tariff_period_id,
                    'actual_from_utc' => $sender->actual_from_utc,
                ] + ($accountTariff->prev_account_tariff_id ? ['package_id' => $accountTariff->id] : []);

            $object = $accountTariff->prev_account_tariff_id ? 'package' : 'service';
            $subAction = $sender->tariff_period_id ? 'tariff_period' : 'off';
            Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + [
                    'action' => "{$object}_{$subAction}_del",
                ]);
        }

        return true;
    }
}
