<?php

namespace app\classes\uu\forms;

use app\classes\Form;
use app\classes\uu\model\Period;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffVoipCity;
use app\exceptions\ModelValidationException;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use InvalidArgumentException;

abstract class TariffForm extends Form
{
    use CrudMultipleTrait;

    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Tariff */
    public $tariff;

    /** @var TariffPeriod[] */
    public $tariffPeriods;

    /** @var TariffResource[] */
    public $tariffResources;

    /** @var TariffVoipCity[] */
    public $tariffVoipCities;

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
                    'is_default' => $post['Tariff']['is_default'],
                ];
            }

            unset($post['TariffPeriod'], $post['TariffResource']);
        }

        switch ($this->tariff->service_type_id) {

            case ServiceType::ID_VPBX:
                // только для ВАТС
                break;

            case ServiceType::ID_VOIP:
            case ServiceType::ID_VOIP_PACKAGE:
                // только для телефонии
                $this->tariffVoipCities = $this->getTariffVoipCities();
                break;
        }

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

                    case ServiceType::ID_VOIP_PACKAGE:
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

                        if ($this->tariff->service_type_id == ServiceType::ID_VOIP_PACKAGE) {
                            $tariffVoipCity = new TariffVoipCity();
                            $tariffVoipCity->tariff_id = $this->id;
                            $this->tariffVoipCities = $this->crudMultipleSelect2($this->tariffVoipCities, $post, $tariffVoipCity, 'city_id');
                        }
                        break;

                    case ServiceType::ID_VOIP:
                        // только для телефонии
                        $tariffVoipCity = new TariffVoipCity();
                        $tariffVoipCity->tariff_id = $this->id;
                        $this->tariffVoipCities = $this->crudMultipleSelect2($this->tariffVoipCities, $post, $tariffVoipCity, 'city_id');
                        break;
                }
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
        // клонировать основной тариф
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

        unset($fieldNames);

        // клонировать города
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

        unset($voipCities, $voipCity, $voipCityCloned, $fieldNames);

        // клонировать периоды
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

        unset($tariffPeriods, $tariffPeriod, $tariffPeriodCloned, $fieldNames);

        // клонировать ресурсы
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

        unset($tariffResources, $tariffResource, $tariffResourceCloned, $fieldNames);

        return $tariffCloned;
    }
}
