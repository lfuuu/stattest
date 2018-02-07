<?php

namespace app\modules\uu\forms;

use app\exceptions\ModelValidationException;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipNdcType;
use InvalidArgumentException;

abstract class TariffForm extends \app\classes\Form
{

    /** @var Tariff */
    public $tariff;

    /** @var TariffPeriod[] */
    public $tariffPeriods;

    /** @var TariffResource[] */
    public $tariffResources;

    /** @var TariffVoipCity[] */
    public $tariffVoipCities;

    /** @var TariffVoipNdcType[] */
    public $tariffNdcTypes;

    /** @var TariffOrganization[] */
    public $tariffOrganizations;

    public $countryId;

    /**
     * @return TariffResource[]
     */
    abstract public function getTariffResources();

    /**
     * @return TariffPeriod[]
     */
    abstract public function getTariffPeriods();

    /**
     * @return TariffVoipCity[]
     */
    abstract public function getTariffVoipCities();

    /**
     * @return TariffVoipNdcType[]
     */
    abstract public function getTariffVoipNdcTypes();

    /**
     * @return TariffOrganization[]
     */
    abstract public function getTariffOrganizations();

    /**
     * @return Tariff
     */
    abstract public function getTariffModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->tariff = $this->getTariffModel();
        $this->tariffPeriods = $this->getTariffPeriods();
        $this->tariffResources = $this->getTariffResources();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * @return TariffPeriod[]
     */
    public function getNewTariffPeriods()
    {
        /** @var Period $period */
        $period = Period::find()->where(['monthscount' => 1])->one();

        $tariffPeriod = new TariffPeriod();
        if ($period) {
            $tariffPeriod->charge_period_id = $period->id;
        }

        $tariffPeriod->price_setup = 0;
        $tariffPeriod->price_min = 0;
        return [$tariffPeriod];
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        $post = \Yii::$app->request->post();
        if ($this->tariff->isHasAccountTariff()) {
            // На этом тарифе есть услуги. Редактировать можно только некоторые свойства.
            if (isset($post['Tariff']['name'])) {
                // checkbox передаются даже disabled, потому что они в паре с hidden. Надо все лишнее убрать
                $post['Tariff'] = [
                    'name' => $post['Tariff']['name'],
                    'tariff_status_id' => $post['Tariff']['tariff_status_id'],
                    'tag_id' => $post['Tariff']['tag_id'],
                    'is_default' => $post['Tariff']['is_default'],
                    'is_charge_after_blocking' => $post['Tariff']['is_charge_after_blocking'],
                ];
            }

            unset($post['TariffPeriod'], $post['TariffResource'], $post['Package']);
        }

        switch ($this->tariff->service_type_id) {

            case ServiceType::ID_VPBX:
                // только для ВАТС
                break;

            case ServiceType::ID_VOIP:
            case ServiceType::ID_VOIP_PACKAGE_CALLS:
                // только для телефонии
                $this->tariffVoipCities = $this->getTariffVoipCities();
                $this->tariffNdcTypes = $this->getTariffVoipNdcTypes();
                break;
        }

        $this->tariffOrganizations = $this->getTariffOrganizations();

        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (isset($post['cloneButton'])) {

                // клонировать тариф
                $tariffCloned = $this->_cloneTariff();
                $this->id = $tariffCloned->id;
                $this->isSaved = true;

            } elseif (isset($post['dropButton'])) {

                // удалить
                $this->tariff->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->tariff->load($post)) {

                if ($this->tariff->is_autoprolongation) {
                    $this->tariff->count_of_validity_period = 0;
                }

                if (!$this->tariff->save()) {
                    $this->validateErrors += $this->tariff->getFirstErrors();
                    throw new ModelValidationException($this->tariff);
                }

                $this->id = $this->tariff->id;
                $this->isSaved = true;

                $tariffPeriod = new TariffPeriod();
                $tariffPeriod->tariff_id = $this->id;
                if (isset($post['TariffPeriod'])) {
                    $this->tariffPeriods = $this->crudMultiple($this->tariffPeriods, $post, $tariffPeriod);
                }

                $tariffResource = new TariffResource();
                $tariffResource->tariff_id = $this->id;
                if (isset($post['TariffResource'])) {
                    $this->tariffResources = $this->crudMultiple($this->tariffResources, $post, $tariffResource);
                }

                switch ($this->tariff->service_type_id) {

                    case ServiceType::ID_VPBX:
                        // только для ВАТС
                        break;

                    case ServiceType::ID_VOIP_PACKAGE_CALLS:
                    case ServiceType::ID_TRUNK_PACKAGE_ORIG:
                    case ServiceType::ID_TRUNK_PACKAGE_TERM:

                        if (!$this->id) {
                            break;
                        }

                        $package = $this->tariff->package;
                        if (!$package) {
                            $package = new Package();
                            $package->tariff_id = $this->id;
                            $package->service_type_id = $this->tariff->service_type_id;
                            $package->is_include_vat = (bool)$this->tariff->is_include_vat;
                            $package->name = $this->tariff->name;
                        }

                        $package->load($post);
                        $package->currency_id = $this->tariff->currency_id;
                        if (!$package->save()) {
                            $this->validateErrors += $package->getFirstErrors();
                            throw new ModelValidationException($package);
                        }

                        $packageMinute = new PackageMinute();
                        $packageMinute->tariff_id = $this->id;
                        $this->crudMultiple($this->tariff->packageMinutes, $post, $packageMinute);

                        $packagePrice = new PackagePrice();
                        $packagePrice->tariff_id = $this->id;
                        $this->crudMultiple($this->tariff->packagePrices, $post, $packagePrice);

                        $packagePricelist = new PackagePricelist();
                        $packagePricelist->tariff_id = $this->id;
                        $this->crudMultiple($this->tariff->packagePricelists, $post, $packagePricelist);

                        if ($this->tariff->service_type_id == ServiceType::ID_VOIP_PACKAGE_CALLS) {
                            $tariffVoipCity = new TariffVoipCity();
                            $tariffVoipCity->tariff_id = $this->id;
                            $this->tariffVoipCities = $this->crudMultipleSelect2($this->tariffVoipCities, $post, $tariffVoipCity, 'city_id');

                            $tariffVoipNdcType = new TariffVoipNdcType();
                            $tariffVoipNdcType->tariff_id = $this->id;
                            $this->tariffNdcTypes = $this->crudMultipleSelect2($this->tariffNdcTypes, $post, $tariffVoipNdcType, 'ndc_type_id');
                        }
                        break;

                    case ServiceType::ID_VOIP:
                        // только для телефонии
                        $tariffVoipCity = new TariffVoipCity();
                        $tariffVoipCity->tariff_id = $this->id;
                        $this->tariffVoipCities = $this->crudMultipleSelect2($this->tariffVoipCities, $post, $tariffVoipCity, 'city_id');

                        $tariffVoipNdcType = new TariffVoipNdcType();
                        $tariffVoipNdcType->tariff_id = $this->id;
                        $this->tariffNdcTypes = $this->crudMultipleSelect2($this->tariffNdcTypes, $post, $tariffVoipNdcType, 'ndc_type_id');
                        break;
                }

                $tariffOrganization = new TariffOrganization();
                $tariffOrganization->tariff_id = $this->id;
                $this->tariffOrganizations = $this->crudMultipleSelect2($this->tariffOrganizations, $post, $tariffOrganization, 'organization_id');
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : \Yii::t('common', 'Internal error');
        }
    }

    /**
     * Клонировать тариф
     *
     * @return Tariff
     * @throws ModelValidationException
     */
    private function _cloneTariff()
    {
        $tariffCloned = $this->_cloneTariffTariff();
        $this->_cloneTariffVoipCity($tariffCloned);
        $this->_cloneTariffVoipNdcType($tariffCloned);
        $this->_cloneTariffOrganization($tariffCloned);
        $this->_cloneTariffPeriod($tariffCloned);
        $this->_cloneTariffResource($tariffCloned);
        $this->_cloneTariffPackage($tariffCloned);
        $this->_cloneTariffPackagePrice($tariffCloned);
        $this->_cloneTariffPackagePricelist($tariffCloned);
        $this->_cloneTariffPackageMinute($tariffCloned);
        return $tariffCloned;
    }

    /**
     * Клонировать тариф. Tariff
     *
     * @return Tariff
     * @throws ModelValidationException
     */
    private function _cloneTariffTariff()
    {
        $tariffCloned = new Tariff();
        $fieldNames = [
            'name',
            'service_type_id',
            'tariff_status_id',
            'currency_id',
            'count_of_validity_period',
            'country_id',
            'tariff_person_id',
            'is_autoprolongation',
            'is_charge_after_blocking',
            'is_include_vat',
            'is_default',
            'is_postpaid',
            'voip_group_id',
            'vm_id',
        ];
        foreach ($fieldNames as $fieldName) {
            $tariffCloned->$fieldName = $this->tariff->$fieldName;
        }

        if (!$tariffCloned->save()) {
            $this->validateErrors += $tariffCloned->getFirstErrors();
            throw new ModelValidationException($tariffCloned);
        }

        return $tariffCloned;
    }

    /**
     * Клонировать тариф. TariffVoipCity
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffVoipCity(Tariff $tariffCloned)
    {
        $voipCities = $this->tariff->voipCities;
        $fieldNames = [
            'city_id',
        ];
        foreach ($voipCities as $voipCity) {
            $voipCityCloned = new TariffVoipCity();
            $voipCityCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $voipCityCloned->$fieldName = $voipCity->$fieldName;
            }

            if (!$voipCityCloned->save()) {
                $this->validateErrors += $voipCityCloned->getFirstErrors();
                throw new ModelValidationException($voipCityCloned);
            }
        }
    }

    /**
     * Клонировать тариф. TariffVoipNdcType
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffVoipNdcType(Tariff $tariffCloned)
    {
        $voipNdcTypes = $this->tariff->voipNdcTypes;
        $fieldNames = [
            'ndc_type_id',
        ];
        foreach ($voipNdcTypes as $voipNdcType) {
            $voipNdcTypeCloned = new TariffVoipNdcType();
            $voipNdcTypeCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $voipNdcTypeCloned->$fieldName = $voipNdcType->$fieldName;
            }

            if (!$voipNdcTypeCloned->save()) {
                $this->validateErrors += $voipNdcTypeCloned->getFirstErrors();
                throw new ModelValidationException($voipNdcTypeCloned);
            }
        }
    }

    /**
     * Клонировать тариф. TariffOrganization
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffOrganization(Tariff $tariffCloned)
    {
        $organizations = $this->tariff->organizations;
        $fieldNames = [
            'organization_id',
        ];
        foreach ($organizations as $organization) {
            $organizationCloned = new TariffOrganization();
            $organizationCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $organizationCloned->$fieldName = $organization->$fieldName;
            }

            if (!$organizationCloned->save()) {
                $this->validateErrors += $organizationCloned->getFirstErrors();
                throw new ModelValidationException($organizationCloned);
            }
        }
    }

    /**
     * Клонировать тариф. TariffPeriod
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffPeriod(Tariff $tariffCloned)
    {
        $tariffPeriods = $this->tariff->tariffPeriods;
        $fieldNames = [
            'price_per_period',
            'price_setup',
            'price_min',
            'charge_period_id',
        ];
        foreach ($tariffPeriods as $tariffPeriod) {
            $tariffPeriodCloned = new TariffPeriod();
            $tariffPeriodCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $tariffPeriodCloned->$fieldName = $tariffPeriod->$fieldName;
            }

            if (!$tariffPeriodCloned->save()) {
                $this->validateErrors += $tariffPeriodCloned->getFirstErrors();
                throw new ModelValidationException($tariffPeriodCloned);
            }
        }
    }

    /**
     * Клонировать тариф. TariffResource
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffResource(Tariff $tariffCloned)
    {
        $tariffResources = $this->tariff->tariffResources;
        $fieldNames = [
            'amount',
            'price_per_unit',
            'price_min',
            'resource_id',
        ];
        foreach ($tariffResources as $tariffResource) {
            $tariffResourceCloned = new TariffResource();
            $tariffResourceCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $tariffResourceCloned->$fieldName = $tariffResource->$fieldName;
            }

            if (!$tariffResourceCloned->save()) {
                $this->validateErrors += $tariffResourceCloned->getFirstErrors();
                throw new ModelValidationException($tariffResourceCloned);
            }
        }
    }

    /**
     * Клонировать тариф. Package
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffPackage(Tariff $tariffCloned)
    {
        $package = $this->tariff->package;
        if (!$package) {
            return;
        }

        $fieldNames = [
            'service_type_id',
            'is_termination',
            'tarification_free_seconds',
            'tarification_interval_seconds',
            'tarification_type',
            'tarification_min_paid_seconds',
            'currency_id',
            'is_include_vat',
            'name',
        ];
        $packageCloned = new Package();
        $packageCloned->tariff_id = $tariffCloned->id;
        foreach ($fieldNames as $fieldName) {
            $packageCloned->$fieldName = $package->$fieldName;
        }

        if (!$packageCloned->save()) {
            $this->validateErrors += $packageCloned->getFirstErrors();
            throw new ModelValidationException($packageCloned);
        }
    }

    /**
     * Клонировать тариф. PackagePrice
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffPackagePrice(Tariff $tariffCloned)
    {
        $packagePrices = $this->tariff->packagePrices;
        $fieldNames = [
            'destination_id',
            'price',
            'interconnect_price',
            'connect_price',
            'weight',
        ];
        foreach ($packagePrices as $packagePrice) {
            $packagePriceCloned = new PackagePrice();
            $packagePriceCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $packagePriceCloned->$fieldName = $packagePrice->$fieldName;
            }

            if (!$packagePriceCloned->save()) {
                $this->validateErrors += $packagePriceCloned->getFirstErrors();
                throw new ModelValidationException($packagePriceCloned);
            }
        }
    }

    /**
     * Клонировать тариф. PackagePricelist
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffPackagePricelist(Tariff $tariffCloned)
    {
        $packagePricelists = $this->tariff->packagePricelists;
        $fieldNames = [
            'pricelist_id',
        ];
        foreach ($packagePricelists as $packagePricelist) {
            $packagePricelistCloned = new PackagePricelist();
            $packagePricelistCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $packagePricelistCloned->$fieldName = $packagePricelist->$fieldName;
            }

            if (!$packagePricelistCloned->save()) {
                $this->validateErrors += $packagePricelistCloned->getFirstErrors();
                throw new ModelValidationException($packagePricelistCloned);
            }
        }
    }

    /**
     * Клонировать тариф. PackageMinute
     *
     * @param Tariff $tariffCloned
     * @throws ModelValidationException
     */
    private function _cloneTariffPackageMinute(Tariff $tariffCloned)
    {
        $packageMinutes = $this->tariff->packageMinutes;
        $fieldNames = [
            'destination_id',
            'minute',
        ];
        foreach ($packageMinutes as $packageMinute) {
            $packageMinuteCloned = new PackageMinute();
            $packageMinuteCloned->tariff_id = $tariffCloned->id;
            foreach ($fieldNames as $fieldName) {
                $packageMinuteCloned->$fieldName = $packageMinute->$fieldName;
            }

            if (!$packageMinuteCloned->save()) {
                $this->validateErrors += $packageMinuteCloned->getFirstErrors();
                throw new ModelValidationException($packageMinuteCloned);
            }
        }
    }
}
