<?php

namespace app\classes\dictionary\forms;

use app\models\City;

class CityFormEdit extends CityForm
{
    /**
     * конструктор
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return City
     */
    public function getCityModel()
    {
        return City::findOne($this->id);
    }
}