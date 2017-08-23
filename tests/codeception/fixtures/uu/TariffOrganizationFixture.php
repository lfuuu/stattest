<?php

namespace app\tests\codeception\fixtures\uu;

use app\modules\uu\models\TariffOrganization;
use app\tests\codeception\fixtures\ActiveFixture;

class TariffOrganizationFixture extends ActiveFixture
{
    public $modelClass = TariffOrganization::class;
}