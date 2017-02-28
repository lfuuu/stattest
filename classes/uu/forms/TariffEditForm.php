<?php

namespace app\classes\uu\forms;

use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffVoipCity;

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
            ->joinWith(['tariffPeriods', 'country', 'status'])
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
}