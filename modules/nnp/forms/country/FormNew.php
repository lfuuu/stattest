<?php

namespace app\modules\nnp\forms\country;

use app\modules\nnp\models\Country;

class FormNew extends Form
{
    /**
     * @return Country
     */
    public function getCountryModel()
    {
        return new Country();
    }
}