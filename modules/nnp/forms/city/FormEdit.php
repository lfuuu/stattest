<?php

namespace app\modules\nnp\forms\city;

use app\modules\nnp\models\City;

class FormEdit extends Form
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