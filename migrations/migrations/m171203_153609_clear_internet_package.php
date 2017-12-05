<?php

use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffResource;

/**
 * Class m171203_153609_clear_internat_package
 */
class m171203_153609_clear_internet_package extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \app\exceptions\ModelValidationException
     */
    public function safeUp()
    {
        $curdate = date(DateTimeZoneHelper::DATE_FORMAT);
        $accountTariffQuery = AccountTariff::find()
            ->where(['service_type_id' => ServiceType::ID_VOIP_PACKAGE_INTERNET]);

        /** @var AccountTariff $accountTariff */
        foreach ($accountTariffQuery->each() as $accountTariff) {
            $accountLogPeriods = $accountTariff->accountLogPeriods;
            foreach ($accountLogPeriods as $accountLogPeriod) {

                if ($accountLogPeriod->date_to <= $curdate) {
                    // уже закончился
                    // просто сжечь сейчас
                    EventQueue::go(
                        \app\modules\mtt\Module::EVENT_CLEAR_INTERNET,
                        [
                            'client_account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->prev_account_tariff_id,
                        ]);
                    continue;
                }

                // еще не закончился
                //
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
                EventQueue::go(
                    \app\modules\mtt\Module::EVENT_CLEAR_INTERNET,
                    [
                        'client_account_id' => $accountTariff->client_account_id,
                        'account_tariff_id' => $accountTariff->prev_account_tariff_id,
                    ],
                    $isForceAdd = false,
                    (new \DateTime($accountLogPeriod->date_to, $accountTariff->clientAccount->getTimezone()))
                        ->modify('+1 day')
                        ->setTime(0, 0, 0)
                        ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
                        ->format(DateTimeZoneHelper::DATETIME_FORMAT)
                );
            }
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
