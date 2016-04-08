<?php

namespace app\classes\dictionary\forms;

use app\models\Country;

class CountryFormEdit extends CountryForm
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
     * @return Country
     */
    public function getCountryModel()
    {
        return Country::findOne($this->id);
    }
}