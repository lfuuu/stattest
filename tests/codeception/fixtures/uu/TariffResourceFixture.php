<?php
namespace app\tests\codeception\fixtures\uu;

use app\tests\codeception\fixtures\ActiveFixture;
use app\modules\uu\models\TariffResource;

class TariffResourceFixture extends ActiveFixture
{
    public $modelClass = TariffResource::class;
}