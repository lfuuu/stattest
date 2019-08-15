<?php

namespace app\modules\uu\resourceReader\PackageCallsResourceReader;

use app\classes\Singleton;
use app\modules\uu\models\AccountTariff;

/**
 * Менеджер для получения параметров траффика
 * @package app\modules\uu\resourceReader\PackageCallsResourceReader
 */
class TrafficParamsManager extends Singleton
{
    const KEY_DEFAULT = 'default';

    protected $trafficParams = [];

    /**
     * Недефолтный объект
     *
     * @param AccountTariff $accountTariff
     * @return TrafficParams|mixed|null
     */
    protected function getCustom(AccountTariff $accountTariff)
    {
        if (array_key_exists($accountTariff->id, $this->trafficParams)) {
            return $this->trafficParams[$accountTariff->id];
        }

        $instance = null;
        switch ($accountTariff->id) {
            case 100008:
                // звонки ориг
                $instance = new TrafficParams($accountTariff);

                $instance->clientAccountId = 57937;
                $instance->prevAccountTariffId = 254172;

                $instance->resultFields = [
                    'nnp_package_price_id' => null,
                    'nnp_package_pricelist_id' => 30,
                ];
                break;

            case 100010:
                // звонки терм
                $instance = new TrafficParams($accountTariff);

                $instance->clientAccountId = 57937;
                $instance->prevAccountTariffId = 254172;

                $instance->resultFields = [
                    'nnp_package_price_id' => null,
                    'nnp_package_pricelist_id' => 31,
                ];
                break;
        }

        if ($instance) {
            $this->trafficParams[$accountTariff->id] = $instance;
        }

        return $instance;
    }

    /**
     * Получить объект с параметрами для универсальной улуги
     *
     * @param AccountTariff $accountTariff
     * @return TrafficParams
     */
    public function getTrafficParams(AccountTariff $accountTariff)
    {
        $instance = null;
        if (YII_ENV_TEST) {
            $instance = $this->getCustom($accountTariff);
        }

        if ($instance) {
            // недефолтный
            return $instance;
        }

        // работаем с дефолным объектом
        if (array_key_exists(self::KEY_DEFAULT, $this->trafficParams)) {
            // обновляем дефолтный объект
            $instance = $this->trafficParams[self::KEY_DEFAULT];
            /** @var TrafficParams $instance */
            $instance->setAccountTariff($accountTariff);
        } else {
            // создаем дефолтный объект
            $instance = new TrafficParams($accountTariff);
            $this->trafficParams[self::KEY_DEFAULT] = $instance;
        }

        return $instance;
    }
}