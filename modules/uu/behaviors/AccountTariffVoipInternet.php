<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffResource;
use DateTimeZone;
use yii\base\Behavior;
use yii\base\Event;


class AccountTariffVoipInternet extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'addVoipInternetPackage',
        ];
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     */
    public function addVoipInternetPackage(Event $event)
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $event->sender;
        $accountTariff = $accountLogPeriod->accountTariff;

        if ($accountTariff->service_type_id != ServiceType::ID_VOIP_PACKAGE_INTERNET) {
            return;
        }

        // Пакет интернета
        /** @var TariffResource $internetTraffic */
        $tariff = $accountLogPeriod->tariffPeriod->tariff;
        $internetTraffic = $tariff->getTariffResource(Resource::ID_VOIP_PACKAGE_INTERNET)->one(); // кол-во предоплаченных мегабайт

        // добавить пакет сейчас
        EventQueue::go(
            \app\modules\mtt\Module::EVENT_ADD_INTERNET,
            [
                'client_account_id' => $accountTariff->client_account_id,
                'account_tariff_id' => $accountTariff->prev_account_tariff_id, // чтобы был правильный порядок выполнения запросов по этой родительской УУ
                'package_account_tariff_id' => $accountTariff->id,
                'internet_traffic' => $internetTraffic->amount * $accountLogPeriod->coefficient, // раз абонентку берем пропорционально оставшимся дням месяца, то и мегабайты тоже надо брать пропорционально меньше
            ]
        );

        // сжечь пакет по окончании периода
        $params = [
            'client_account_id' => $accountTariff->client_account_id,
            'account_tariff_id' => $accountTariff->prev_account_tariff_id,
        ];
        $isForceAdd = false;
        $nextStart = (new \DateTime($accountLogPeriod->date_to, $accountTariff->clientAccount->getTimezone()))
            ->modify('+1 day')
            ->setTime(0, 0, 0)
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        EventQueue::go(\app\modules\mtt\Module::EVENT_CLEAR_BALANCE, $params, $isForceAdd, $nextStart);
        EventQueue::go(\app\modules\mtt\Module::EVENT_CLEAR_INTERNET, $params, $isForceAdd, $nextStart);
    }
}
