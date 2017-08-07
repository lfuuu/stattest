<?php
namespace app\tests\codeception\fixtures;

use app\models\TariffVoipPackage;

class TariffVoipPackageFixture extends ActiveFixture
{
    public $modelClass = TariffVoipPackage::class;
    public $depends = [
        DestinationFixture::class,
    ];
}