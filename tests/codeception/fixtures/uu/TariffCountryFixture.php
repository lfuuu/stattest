<?php

namespace app\tests\codeception\fixtures\uu;

use app\modules\uu\models\TariffCountry;
use app\tests\codeception\fixtures\ActiveFixture;

class TariffCountryFixture extends ActiveFixture
{
    public $modelClass = TariffCountry::class;
}