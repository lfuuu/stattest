<?php

namespace app\modules\nnp2\forms\city;

use app\modules\nnp2\models\City;

class FormEdit extends Form
{
    /**
     * Конструктор
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