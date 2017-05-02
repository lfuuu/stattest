<?php
namespace app\tests\codeception\fixtures\uu;

use app\tests\codeception\fixtures\ActiveFixture;
use app\modules\uu\models\TariffPeriod;

class TariffPeriodFixture extends ActiveFixture
{
    public $modelClass = TariffPeriod::class;
}