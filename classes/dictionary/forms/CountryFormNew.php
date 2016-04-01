<?php

namespace app\classes\dictionary\forms;

use app\models\Country;

class CountryFormNew extends CountryForm
{
    /**
     * @return Country
     */
    public function getCountryModel()
    {
        $country = new Country();
        $country->in_use = true;
        return $country;
    }
}