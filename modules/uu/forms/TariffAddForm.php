<?php

namespace app\modules\uu\forms;

use app\models\Currency;
use app\modules\nnp\models\PackageApi;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffBundle;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\TariffTags;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipNdcType;
use app\modules\uu\models\TariffVoipSource;

class TariffAddForm extends TariffForm
{
    public $serviceTypeId;

    /**
     * Конструктор
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
        $tariff->currency_id = Currency::RUB;
        $tariff->tariff_status_id = TariffStatus::ID_PUBLIC;
        $tariff->count_of_validity_period = 0;
        $tariff->is_include_vat = 1;
        $tariff->is_autoprolongation = 1;
        return $tariff;
    }

    /**
     * @return TariffResource[]
     */
    public function getTariffResources()
    {
        $tariffResources = [];
        $resources = ResourceModel::findAll(['service_type_id' => $this->serviceTypeId]);
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

    /**
     * @return TariffVoipNdcType[]
     */
    public function getTariffVoipNdcTypes()
    {
        return [new TariffVoipNdcType()];
    }

    /**
     * @return TariffVoipSource[]
     */
    public function getTariffVoipSources()
    {
        return [new TariffVoipSource()];
    }

    /**
     * @return TariffOrganization[]
     */
    public function getTariffOrganizations()
    {
        return [new TariffOrganization()];
    }

    /**
     * @return TariffBundle[]
     */
    public function getTariffBundles()
    {
        return [new TariffBundle()];
    }

    /**
     * @return TariffCountry[]
     */
    public function getTariffCountries()
    {
        return [new TariffCountry()];
    }

    /**
     * @return TariffVoipCountry[]
     */
    public function getTariffVoipCountries()
    {
        return [new TariffVoipCountry()];
    }

    /**
     * @return TariffTags[]
     */
    public function getTariffTags()
    {
        return [new TariffTags()];
    }

    /**
     * @return PackageApi
     */
    public function getPackageApi()
    {
        return [new PackageApi()];
    }
}