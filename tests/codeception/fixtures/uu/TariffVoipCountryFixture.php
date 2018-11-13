<?php

namespace app\tests\codeception\fixtures\uu;

use app\modules\uu\models\TariffVoipCountry;
use app\tests\codeception\fixtures\ActiveFixture;

class TariffVoipCountryFixture extends ActiveFixture
{
    public $modelClass = TariffVoipCountry::class;
}