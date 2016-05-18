<?php

namespace app\classes\dictionary\forms;

use app\models\City;

class CityFormNew extends CityForm
{
    /**
     * @return City
     */
    public function getCityModel()
    {
        return new City();
    }
}