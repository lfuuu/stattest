<?php
namespace app\tests\codeception\fixtures\uu;

use app\tests\codeception\fixtures\ActiveFixture;
use app\modules\uu\models\Tariff;

class TariffFixture extends ActiveFixture
{
    public $modelClass = Tariff::class;
}