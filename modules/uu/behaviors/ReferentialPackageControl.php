<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\base\Event;

class ReferentialPackageControl extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'insertAccountTariffLog',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteAccountTariffLog',
        ];
    }

    /**
     * Установить выключение пакетов вместе с выключением услуги
     *
     * @param Event $event
     */
    public function insertAccountTariffLog(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;

        // обрабатываем только закрытие
        if ($accountTariffLog->tariff_period_id) {
            return;
        }

        $accountTariff = $accountTariffLog->accountTariff;

        // only main service
        if (!isset(ServiceType::$serviceToPackage[$accountTariff->service_type_id])) {
            return;
        }

        $data = [
            'client_account_id' => $accountTariff->client_account_id,
            'account_tariff_id' => $accountTariff->id,
            'account_tariff_log_id' => $accountTariffLog->id,
        ];

//        EventQueue::go(Module::EVENT_CLOSE_ALL_PACKAGE, $data);
        AccountTariff::closeAllPackages($data);
    }

    /**
     * Удалить не включенные пакеты при удалении услуги
     *
     * @param Event $event
     */
    public function deleteAccountTariffLog(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $accountTariff = $accountTariffLog->accountTariff;

        if (count($accountTariff->accountTariffLogs)) {
            // это не удаление услуги, а удаление смены тарифа

            AccountTariff::getDb()->transaction(function ($db) use ($accountTariff, $accountTariffLog) {
                // удаляем только те логи ресурсов, которые в это же время или позже сделаны чем удаляемый лог смены тарифа
                $nextAccountTariffs = $accountTariff->nextAccountTariffs;
                foreach ($nextAccountTariffs as $nextAccountTariff) {
                    $nextAccountTariffLogs = $nextAccountTariff->accountTariffLogs;

                    $nextAccountTariffLog = end($nextAccountTariffLogs);
                    // удаляем не включенные в будущем пакеты
                    if ($nextAccountTariffLog->actual_from_utc >= $accountTariffLog->actual_from_utc) {
                        if (!$nextAccountTariff->delete()) {
                            throw new ModelValidationException($accountTariffLog);
                        }
                        continue;
                    }

                    $nextAccountTariffLog = reset($nextAccountTariffLogs);

                    do {
                        if ($nextAccountTariffLog->actual_from_utc >= $accountTariffLog->actual_from_utc) {
                            if (!$nextAccountTariffLog->delete()) {
                                throw new ModelValidationException($accountTariffLog);
                            }
                        }

                    } while ($nextAccountTariffLog = next($nextAccountTariffLogs));
                }
            });
        }
    }
}
