<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\ServiceType;
use DateTimeZone;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\Expression;

/**
 * Синхронизировать данные в AccountTariffLight
 * Не сразу, а через очередь - потому что в разных БД на разных серверах (mysql и postrgesql)
 */
class SyncAccountTariffLight extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'accountLogPeriodChange',
            ActiveRecord::EVENT_AFTER_UPDATE => 'accountLogPeriodChange',
            ActiveRecord::EVENT_AFTER_DELETE => 'accountLogPeriodDelete',
        ];
    }

    /**
     * Триггер при изменении (добавлении/редактировании) списания абонентки
     *
     * @param Event $event
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     */
    public function accountLogPeriodChange(Event $event)
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $event->sender;
        $accountTariff = $accountLogPeriod->accountTariff;
        if (!array_key_exists($accountTariff->service_type_id, ServiceType::$packages)) {
            // только для пакетов
            return;
        }

        $clientTimezone = $accountTariff->clientAccount->getTimezone();
        $utcTimezone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);

        $activateFrom = (new \DateTimeImmutable($accountLogPeriod->date_from, $clientTimezone))
            ->setTimezone($utcTimezone)
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $coefficient = $accountLogPeriod->coefficient;

        $deactivateFrom = (new \DateTimeImmutable($accountLogPeriod->date_to, $clientTimezone))
            ->setTimezone($utcTimezone)
            ->modify('+1 day')// в AccountLogPeriod указан последний день действия, то есть выключить надо не в этот день, а только после его окончания (на следующий день)
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if (!$accountTariff->prev_account_tariff_id) {
            throw new \LogicException('Универсальная услуга ' . $accountTariff->id . ' пакета телефонии не привязана к основной услуге телефонии');
        }

        \app\classes\Event::go(\app\modules\uu\Module::EVENT_ADD_LIGHT,
            [
                'id' => $accountLogPeriod->id,
                'account_client_id' => $accountTariff->client_account_id,
                'tariff_id' => $accountLogPeriod->tariffPeriod->tariff_id,
                'activate_from' => $activateFrom,
                'deactivate_from' => $deactivateFrom,
                'coefficient' => $coefficient,
                'account_tariff_id' => $accountTariff->prevAccountTariff->id,
                'price' => ($accountLogPeriod->tariffPeriod->price_setup + $accountLogPeriod->tariffPeriod->price_per_period), // чтобы учесть и разовые услуги (price_setup), и обычные (price_per_period)
                'service_type_id' => $accountTariff->service_type_id,
            ]
        );

    }

    /**
     * Триггер при удалении списания абонентки
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function accountLogPeriodDelete(Event $event)
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $event->sender;
        $accountTariff = $accountLogPeriod->accountTariff;
        if (!array_key_exists($accountTariff->service_type_id, ServiceType::$packages) || $accountTariff->service_type_id == ServiceType::ID_VOIP_PACKAGE_INTERNET) {
            // только для пакетов телефонии, кроме интернета
            return;
        }

        \app\classes\Event::go(\app\modules\uu\Module::EVENT_DELETE_LIGHT,
            [
                'id' => $accountLogPeriod->id,
            ]
        );

    }

    /**
     * Добавить данные в AccountTariffLight
     *
     * @param array $params [id, account_client_id, tariff_id, activate_from, deactivate_from, coefficient, account_tariff_id, tariffication_by_minutes, tariffication_full_first_minute, tariffication_free_first_seconds, price]
     * @throws \Exception
     */
    public static function addToAccountTariffLight(array $params)
    {
        $accountTariffLight = AccountTariffLight::findOne(['id' => $params['id']]);
        if (!$accountTariffLight) {
            $accountTariffLight = new AccountTariffLight;
            $accountTariffLight->id = $params['id'];
        }

        $accountTariffLight->account_client_id = $params['account_client_id'];
        $accountTariffLight->tariff_id = $params['tariff_id'];
        $accountTariffLight->activate_from = new Expression(sprintf("TIMESTAMP '%s'", $params['activate_from']));
        $accountTariffLight->deactivate_from = $params['deactivate_from'] ? new Expression(sprintf("TIMESTAMP '%s'", $params['deactivate_from'])) : null;
        $accountTariffLight->coefficient = str_replace(',', '.', $params['coefficient']);
        $accountTariffLight->account_tariff_id = $params['account_tariff_id'];
        $accountTariffLight->price = $params['price'];
        $accountTariffLight->service_type_id = $params['service_type_id'];
        if (!$accountTariffLight->save()) {
            throw new ModelValidationException($accountTariffLight);
        }
    }

    /**
     * Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
     *
     * @param array $params [id]
     * @throws \Exception
     */
    public static function deleteFromAccountTariffLight(array $params)
    {
        $accountTariffLight = AccountTariffLight::findOne(['id' => $params['id']]);
        if ($accountTariffLight && !$accountTariffLight->delete()) {
            throw new ModelValidationException($accountTariffLight);
        }
    }
}
