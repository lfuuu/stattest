<?php

namespace app\modules\nnp\forms\country;

use app\modules\nnp\models\Country;

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
     * @return Country
     */
    public function getCountryModel()
    {
        return Country::findOne(['code' => $this->id]);
    }
}