<?php

namespace app\modules\uu\behaviors;

use app\exceptions\ModelValidationException;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


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

        $readerNames = Resource::getReaderNames();
        $tariff = $accountTariffLog->tariffPeriod->tariff;
        $tariffResources = $tariff->tariffResources;
        foreach ($tariffResources as $tariffResource) {

            if (array_key_exists($tariffResource->resource_id, $readerNames)) {
                // этот ресурс - не опция. Он считается по факту, а не заранее
                continue;
            }

            $accountTariffResourceLog = new AccountTariffResourceLog;
            $accountTariffResourceLog->account_tariff_id = $accountTariffLog->account_tariff_id;
            $accountTariffResourceLog->actual_from_utc = $accountTariffLog->actual_from_utc;
            $accountTariffResourceLog->resource_id = $tariffResource->resource_id;

            /** @var \app\models\Number $number */
            if ($tariffResource->resource_id == Resource::ID_VOIP_FMC && ($number = $accountTariff->number) && $number->isFmcAlwaysActive()) {
                // Костыль для FMC. Включенность этого ресурса зависит от типа телефонного номера
                $accountTariffResourceLog->amount = 1;
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
     * @throws \app\exceptions\ModelValidationException
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

        $accountTariffResourceLogs = $accountTariff->accountTariffResourceLogs;
        foreach ($accountTariffResourceLogs as $accountTariffResourceLog) {
            if (!$accountTariffResourceLog->delete()) {
                throw new ModelValidationException($accountTariffResourceLog);
            }
        }
    }
}
