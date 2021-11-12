<?php

namespace app\modules\uu\forms;

use app\modules\nnp\models\PackageApi;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffBundle;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffTags;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipNdcType;

class TariffEditForm extends TariffForm
{
    /**
     * Конструктор
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter tariff'));
        }

        parent::init();
    }

    /**
     * @return Tariff
     */
    public function getTariffModel()
    {
        $tariffTableName = Tariff::tableName();

        /** @var Tariff $tariff */
        $tariff = Tariff::find()
            ->where($tariffTableName . '.id = :id', [':id' => $this->id])
            ->joinWith(['tariffPeriods', 'status'])
            ->one();
        if (!$tariff) {
            throw new \InvalidArgumentException(\Yii::t('common', 'Wrong ID'));
        }

        return $tariff;
    }

    /**
     * @return TariffResource[]
     */
    public function getTariffResources()
    {
        return $this->tariff->tariffResources;
    }

    /**
     * @return TariffPeriod[]
     */
    public function getTariffPeriods()
    {
        $tariffPeriods = $this->tariff->tariffPeriods;
        return $tariffPeriods ?: $this->getNewTariffPeriods();
    }

    /**
     * @return TariffVoipCity[]
     */
    public function getTariffVoipCities()
    {
        return $this->tariff->voipCities;
    }

    /**
     * @return TariffVoipNdcType[]
     */
    public function getTariffVoipNdcTypes()
    {
        return $this->tariff->voipNdcTypes;
    }

    /**
     * @return TariffOrganization[]
     */
    public function getTariffOrganizations()
    {
        return $this->tariff->organizations;
    }

    /**
     * @return TariffBundle[]
     */
    public function getTariffBundles()
    {
        return $this->tariff->bundleTariffs;
    }

    /**
     * @return TariffTags[]
     */
    public function getTariffTags()
    {
        return $this->tariff->tags;
    }

    /**
     * @return TariffCountry[]
     */
    public function getTariffCountries()
    {
        return $this->tariff->tariffCountries;
    }

    /**
     * @return TariffVoipCountry[]
     */
    public function getTariffVoipCountries()
    {
        return $this->tariff->tariffVoipCountries;
    }

    /**
     * @return PackageApi[]
     */
    public function getPackageApi()
    {
        return $this->tariff->packageApi;
    }
}