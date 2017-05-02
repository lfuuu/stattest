<?php
namespace app\tests\codeception\fixtures\uu;

use app\tests\codeception\fixtures\ActiveFixture;
use app\modules\uu\models\AccountTariffResourceLog;

class AccountTariffResourceLogFixture extends ActiveFixture
{
    public $modelClass = AccountTariffResourceLog::class;
}