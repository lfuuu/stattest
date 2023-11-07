<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\models\NumberLog;
use app\modules\uu\models\AccountTariff;
use yii\base\Behavior;
use yii\base\Event;


class AccountTariffVoipNumber extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'setVoipNumberStatus',
        ];
    }

    /**
     * Предварительно захватить номер себе, чтобы повторно нельзя было подключить ни на эту, ни на другую услугу
     * Окончательный захват будет в SetCurrentTariffTarificator и \app\models\Number::dao()->actualizeStatusByE164
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function setVoipNumberStatus(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        /** @var \app\models\Number $number */
        if (!$accountTariff->voip_number || !($number = $accountTariff->number)) {
            return;
        }

        // не надо захватывать номер с помощью статуса, если номер и так используется. Это позволит иметь правильный статус номера.
        if (in_array($number->status, Number::$statusGroup[Number::STATUS_GROUP_ACTIVE])) {
            return;
        }

        $number->is_verified = null;
        $number->status = Number::STATUS_ACTIVE_CONNECTED;
        $number->uu_account_tariff_id = $accountTariff->id;
        $number->client_id = $accountTariff->client_account_id;

        if (!$number->save()) {
            throw new ModelValidationException($number);
        }

        Number::dao()->log(
            $number,
            NumberLog::ACTION_CONNECTED,
            $accountTariff->id
        );
    }
}
