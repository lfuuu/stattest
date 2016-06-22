<?php

namespace app\forms\dictonary\city_billing_method;

use app\models\CityBillingMethod;

class CityBillingMethodEdit extends CityBillingMethodForm
{

    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return CityBillingMethod
     */
    public function getRecordModel()
    {
        return CityBillingMethod::findOne($this->id);
    }
}