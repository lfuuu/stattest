<?php

namespace app\classes\behaviors\uu;

use app\classes\DateTimeWithUserTimezone;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\ServiceType;
use app\modules\nnp\models\AccountTariffLight;
use DateTimeZone;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Синхронизировать данные в AccountTariffLight
 * Не сразу, а через очередь - потому что в разных БД на разных серверах (mysql и postrgesql)
 */
class SyncAccountTariffLight extends Behavior
{
    const EVENT_ADD_TO_ACCOUNT_TARIFF_LIGHT = 'add_to_account_tariff_light';
    const EVENT_DELETE_FROM_ACCOUNT_TARIFF_LIGHT = 'delete_from_account_tariff_light';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'AccountLogPeriodChange',
            ActiveRecord::EVENT_AFTER_UPDATE => 'AccountLogPeriodChange',
            ActiveRecord::EVENT_AFTER_DELETE => 'AccountLogPeriodDelete',
        ];
    }

    /**
     * Триггер при изменении (добавлении/редактировании) списания абонентки
     * @param Event $event
     */
    public function AccountLogPeriodChange(Event $event)
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $event->sender;
        $accountTariff = $accountLogPeriod->accountTariff;
        if ($accountTariff->service_type_id != ServiceType::ID_VOIP_PACKAGE) {
            // только для пакетов
            return;
        }

        $clientTimezoneName = $accountTariff->clientAccount->timezone_name;
        $clientTimezone = new DateTimeZone($clientTimezoneName);
        $utcTimezone = new DateTimeZone(DateTimeWithUserTimezone::TIMEZONE_UTC);

        $activateFrom = (new \DateTimeImmutable($accountLogPeriod->date_from, $clientTimezone))
            ->setTimezone($utcTimezone)
            ->format('U');

        $activateTo = (new \DateTimeImmutable($accountLogPeriod->date_to, $clientTimezone))
            ->setTimezone($utcTimezone)
            ->format('U');

        if (!$accountTariff->prev_account_tariff_id) {
            throw new \LogicException('Универсальная услуга ' . $accountTariff->id . ' пакета телефонии не привязана к основной услуге телефонии');
        }

        \app\classes\Event::go(self::EVENT_ADD_TO_ACCOUNT_TARIFF_LIGHT, [
                'id' => $accountLogPeriod->id,
                'number' => $accountTariff->prevAccountTariff->voip_number,
                'account_client_id' => $accountTariff->client_account_id,
                'tariff_id' => $accountTariff->tariffPeriod->tariff_id,
                'activate_from' => $activateFrom,
                'deactivate_from' => $activateTo,
            ]
        );

    }

    /**
     * Триггер при удалении списания абонентки
     * @param Event $event
     */
    public function AccountLogPeriodDelete(Event $event)
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $event->sender;
        $accountTariff = $accountLogPeriod->accountTariff;
        if ($accountTariff->service_type_id != ServiceType::ID_VOIP_PACKAGE) {
            // только для пакетов
            return;
        }

        \app\classes\Event::go(self::EVENT_DELETE_FROM_ACCOUNT_TARIFF_LIGHT, [
                'id' => $accountLogPeriod->id,
            ]
        );

    }

    /**
     * Добавить данные в AccountTariffLight
     * @param array $params [id, number, account_client_id, tariff_id, activate_from, deactivate_from]
     * @throws \Exception
     * @internal param AccountLogPeriod $accountLogPeriod
     */
    public static function addToAccountTariffLight(array $params)
    {
        $accountTariffLight = AccountTariffLight::findOne(['id' => $params['id']]);
        if (!$accountTariffLight) {
            $accountTariffLight = new AccountTariffLight;
            $accountTariffLight->id = $params['id'];
        }
        $accountTariffLight->number = $params['number'];
        $accountTariffLight->account_client_id = $params['account_client_id'];
        $accountTariffLight->tariff_id = $params['tariff_id'];
        $accountTariffLight->activate_from = $params['activate_from'];
        $accountTariffLight->deactivate_from = $params['deactivate_from'];
        if (!$accountTariffLight->save()) {
            throw new \Exception(implode(' ', $accountTariffLight->getFirstErrors()));
        }
    }

    /**
     * Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
     * @param array $params [id]
     * @throws \Exception
     * @internal param AccountLogPeriod $accountLogPeriod
     */
    public static function deleteFromAccountTariffLight(array $params)
    {
        $accountTariffLight = AccountTariffLight::findOne(['id' => $params['id']]);
        if ($accountTariffLight && !$accountTariffLight->delete()) {
            throw new \Exception(implode(' ', $accountTariffLight->getFirstErrors()));
        }
    }
}
