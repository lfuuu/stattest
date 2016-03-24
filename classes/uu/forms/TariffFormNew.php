<?php

namespace app\classes\uu\forms;

use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffVoipCity;

class TariffFormNew extends TariffForm
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
        return $tariff;
    }

    /**
     * @return TariffResource[]
     */
    public function getTariffResources()
    {
        $models = [];
        $resources = Resource::findAll(['service_type_id' => $this->serviceTypeId]);
        foreach ($resources as $resource) {
            $model = new TariffResource();
            $model->resource_id = $resource->id;
            $models[] = $model;
        }
        return $models;
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