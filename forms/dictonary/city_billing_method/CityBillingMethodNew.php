<?php

namespace app\forms\dictonary\city_billing_method;

use app\models\CityBillingMethod;

class CityBillingMethodNew extends CityBillingMethodForm
{
    /**
     * @return CityBillingMethod
     */
    public function getRecordModel()
    {
        return new CityBillingMethod;
    }
}