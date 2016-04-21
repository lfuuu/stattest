<?php

namespace app\classes\uu\forms;

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;
use app\models\Country;
use app\models\Currency;

class TariffAddForm extends TariffForm
{
    public $serviceTypeId;

    /**
     * конструктор
     */
    public function init()
    {
        if ($this->serviceTypeId === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter usage type'));
        }

        parent::init();
    }

    /**
     * @return Tariff
     */
    public function getTariffModel()
    {
        $tariff = new Tariff();
        $tariff->service_type_id = $this->serviceTypeId;
        $tariff->country_id = Country::RUSSIA;
        $tariff->currency_id = Currency::RUB;
        $tariff->tariff_status_id = TariffStatus::ID_PUBLIC;
        $tariff->count_of_validity_period = 0;
        $tariff->is_autoprolongation = 1;
        return $tariff;
    }

    /**
     * @return TariffResource[]
     */
    public function getTariffResources()
    {
        $tariffResources = [];
        $resources = Resource::findAll(['service_type_id' => $this->serviceTypeId]);
        foreach ($resources as $resource) {
            $tariffResource = new TariffResource();
            $tariffResource->resource_id = $resource->id;
            $tariffResource->amount = 0;
            $tariffResource->price_min = 0;
            $tariffResources[] = $tariffResource;
        }
        return $tariffResources;
    }

    /**
     * @return TariffPeriod[]
     */
    public function getTariffPeriods()
    {
        return $this->getNewTariffPeriods();
    }

    /**
     * @return TariffVoipCity[]
     */
    public function getTariffVoipCities()
    {
        return [new TariffVoipCity()];
    }
}