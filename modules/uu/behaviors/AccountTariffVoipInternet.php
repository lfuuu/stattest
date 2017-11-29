<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffResource;
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

        \app\classes\Event::go(\app\modules\uu\Module::EVENT_VOIP_INTERNET, [
            'account_id' => $accountTariff->client_account_id,
            'account_tariff_id' => $accountTariff->id,
            'internet_traffic' => $internetTraffic->amount * $accountLogPeriod->coefficient, // раз абонентку берем пропорционально оставшимся дням месяца, то и мегабайты тоже надо брать пропорционально меньше
        ]);
    }
}
