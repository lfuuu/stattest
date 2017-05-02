<?php
namespace app\tests\codeception\fixtures\uu;

use app\tests\codeception\fixtures\ActiveFixture;
use app\modules\uu\models\AccountTariff;

class AccountTariffFixture extends ActiveFixture
{
    public $modelClass = AccountTariff::class;
}