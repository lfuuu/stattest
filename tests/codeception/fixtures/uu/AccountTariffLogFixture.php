<?php
namespace app\tests\codeception\fixtures\uu;

use app\tests\codeception\fixtures\ActiveFixture;
use app\modules\uu\models\AccountTariffLog;

class AccountTariffLogFixture extends ActiveFixture
{
    public $modelClass = AccountTariffLog::class;
}