<?php

namespace app\modules\nnp\forms\city;

use app\modules\nnp\models\City;

class FormNew extends Form
{
    /**
     * @return City
     */
    public function getCityModel()
    {
        return new City();
    }
}