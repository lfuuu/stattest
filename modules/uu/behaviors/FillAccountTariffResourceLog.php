<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceClass;
use yii\base\Behavior;
use yii\base\Event;

class FillAccountTariffResourceLog extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'insertIntoAccountTariffResourceLog',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteFromAccountTariffResourceLog',
        ];
    }

    /**
     * Создать лог ресурсов при создании услуги
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function insertIntoAccountTariffResourceLog(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;

        if (!$accountTariffLog->tariff_period_id) {
            // закрыть услугу
            return;
        }

        $accountTariff = $accountTariffLog->accountTariff;
        unset($accountTariff->accountTariffLogs); // сбросить relation, чтобы он заново построился
        if (count($accountTariff->accountTariffLogs) != 1) {
            // это не создание услуги, а смена тарифа
            return;
        }

        /** @var \app\models\Number $number */
        $number = $number = $accountTariff->number;

        $tariff = $accountTariffLog->tariffPeriod->tariff;
        $tariffResources = $tariff->tariffResources;
        foreach ($tariffResources as $tariffResource) {

            if (!ResourceClass::isOptionId($tariffResource->resource_id)) {
                // этот ресурс - не опция. Он считается по факту, а не заранее
                continue;
            }

            $accountTariffResourceLog = new AccountTariffResourceLog;
            $accountTariffResourceLog->account_tariff_id = $accountTariffLog->account_tariff_id;
            $accountTariffResourceLog->actual_from_utc = $accountTariffLog->actual_from_utc;
            $accountTariffResourceLog->resource_id = $tariffResource->resource_id;

            if ($tariffResource->resource_id == ResourceClass::ID_VOIP_FMC && $number) {
                // Костыль для FMC. Включенность этого ресурса зависит от типа телефонного номера
                $isFmcActive = $number->isFmcAlwaysActive() || (!$number->isFmcAlwaysInactive() && $tariffResource->amount);
                $accountTariffResourceLog->amount = (int) $isFmcActive;

            } elseif ($tariffResource->resource_id == ResourceClass::ID_VOIP_MOBILE_OUTBOUND && $number) {
                // Костыль для Исх.Моб.Связь. Включенность этого ресурса зависит от типа телефонного номера
                $isMobileOutboundActive = $number->isMobileOutboundAlwaysActive() || (!$number->isMobileOutboundAlwaysInactive() && $tariffResource->amount);
                $accountTariffResourceLog->amount = (int) $isMobileOutboundActive;

            } else {
                $accountTariffResourceLog->amount = $tariffResource->amount;
            }

            if (!$accountTariffResourceLog->save()) {
                throw new ModelValidationException($accountTariffResourceLog);
            }
        }
    }

    /**
     * Удалить лог ресурсов при удалении услуги
     *
     * @param Event $event
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteFromAccountTariffResourceLog(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $accountTariff = $accountTariffLog->accountTariff;

        if (count($accountTariff->accountTariffLogs)) {
            // это не удаление услуги, а удаление смены тарифа
            return;
        }

        $accountTariffResourceLogs = $accountTariff->accountTariffResourceLogsAll;
        foreach ($accountTariffResourceLogs as $accountTariffResourceLog) {
            if (!$accountTariffResourceLog->delete()) {
                throw new ModelValidationException($accountTariffResourceLog);
            }
        }
    }
}
