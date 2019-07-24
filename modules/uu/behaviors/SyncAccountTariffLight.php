<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
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
     * @throws ModelValidationException
     * @throws \yii\base\Exception
     */
    public function accountLogPeriodChange(Event $event)
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $event->sender;
        $accountTariff = $accountLogPeriod->accountTariff;
        if (
            !array_key_exists($accountTariff->service_type_id, ServiceType::$packages)
            || $accountTariff->service_type_id == ServiceType::ID_VOIP_PACKAGE_INTERNET
        ) {
            // только для пакетов телефонии, кроме интернета, но не Roamobility
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

        EventQueue::go(\app\modules\uu\Module::EVENT_ADD_LIGHT,
            [
                'id' => $accountLogPeriod->id,
                'client_account_id' => $accountTariff->client_account_id,
                'tariff_id' => $accountLogPeriod->tariffPeriod->tariff_id,
                'activate_from' => $activateFrom,
                'deactivate_from' => $deactivateFrom,
                'coefficient' => $coefficient,
                'account_tariff_id' => $accountTariff->prevAccountTariff->id,
                'account_package_id' => $accountTariff->id,
                'price' => ($accountLogPeriod->tariffPeriod->price_setup + $accountLogPeriod->tariffPeriod->price_per_period), // чтобы учесть и разовые услуги (price_setup), и обычные (price_per_period)
                'service_type_id' => $accountTariff->service_type_id,
            ]
        );

    }

    /**
     * Триггер при удалении списания абонентки
     *
     * @param Event $event
     * @throws ModelValidationException
     * @throws \yii\base\Exception
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

        EventQueue::go(\app\modules\uu\Module::EVENT_DELETE_LIGHT,
            [
                'id' => $accountLogPeriod->id,
                'account_tariff_id' => $accountTariff->id,
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

        $accountTariffLight->account_client_id = $params['client_account_id'];
        $accountTariffLight->tariff_id = $params['tariff_id'];
        $accountTariffLight->activate_from = new Expression(sprintf("TIMESTAMP '%s'", $params['activate_from']));
        $accountTariffLight->deactivate_from = $params['deactivate_from'] ? new Expression(sprintf("TIMESTAMP '%s'", $params['deactivate_from'])) : null;
        $accountTariffLight->coefficient = str_replace(',', '.', $params['coefficient']);
        $accountTariffLight->account_tariff_id = $params['account_tariff_id'];
        $accountTariffLight->account_package_id = $params['account_package_id'];
        $accountTariffLight->price = $params['price'];
        $accountTariffLight->service_type_id = $params['service_type_id'];
        if (!$accountTariffLight->save()) {
            throw new ModelValidationException($accountTariffLight);
        }
    }

    /**
     * Закрыть пакет в AccountTariffLight
     *
     * @param array $params [client_account_id, account_tariff_id]
     * @throws \Exception
     */
    public static function closeAccountTariffLight(array $params)
    {
        $currentDate = date(DateTimeZoneHelper::DATE_FORMAT);

        // найти оплаченные периоды в будущем
        // такое возможно только, когда менеджер (но не сам юзер) закрывает пакет раньше оплаченного срока
        /** @var AccountLogPeriod[] $accountLogPeriods */
        $accountLogPeriods = AccountLogPeriod::find()
            ->where(['account_tariff_id' => $params['account_tariff_id']])
            ->andWhere(['>', 'date_to', $currentDate])
            ->all();
        foreach ($accountLogPeriods as $accountLogPeriod) {
            $accountTariffLight = AccountTariffLight::findOne(['id' => $accountLogPeriod->id]);
            if (!$accountTariffLight) {
                continue;
            }

            $accountTariffLight->deactivate_from = $currentDate; // закрыть раньше оплаченного времени
            if (!$accountTariffLight->save()) {
                throw new ModelValidationException($accountTariffLight);
            }
        }
    }

    /**
     * Удалить данные из AccountTariffLight. Теоретически этого быть не должно, но...
     *
     * @param array $params [id]
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function deleteFromAccountTariffLight(array $params)
    {
        $accountTariffLight = AccountTariffLight::findOne(['id' => $params['id']]);
        if ($accountTariffLight && !$accountTariffLight->delete()) {
            throw new ModelValidationException($accountTariffLight);
        }
    }

    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return strpos(AccountTariffLight::getDb()->username, 'readonly') === false;
    }
}
