<?php


namespace tests\codeception\func;

use app\forms\client\ClientCreateExternalForm;
use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Currency;
use app\models\Number;
use app\models\UsageVoip;

class _NumberCycleHelper
{

    /** @var \_FuncTester $I */
    private $I = null;

    public function __construct(&$_I)
    {
        $this->I = &$_I;
    }


    /**
     * Создание пустого ЛС
     *
     * @return ClientAccount
     */
    function createSingleClientAccount()
    {
        $clientForm = new ClientCreateExternalForm();
        $clientForm->company = 'test account ' . mt_rand(0, 1000);
        if (!$clientForm->create()) {
            $this->fail('Cant create client account');
        }

        return ClientAccount::findOne(['id' => $clientForm->account_id]);
    }

    /**
     * Создание улуги телефонии
     *
     * @param ClientAccount $clientAccount
     * @param Number $number
     * @param $tariffMainId
     * @return UsageVoip
     */
    function createUsage(ClientAccount $clientAccount, \app\models\Number $number, $tariffMainId)
    {
        $form = new UsageVoipEditForm();
        $form->scenario = 'add';
        $form->timezone = $clientAccount->timezone_name;
        $form->initModel($clientAccount);
        $form->did = $number->number;
        $form->tariff_main_id = $tariffMainId;
        $form->prepareAdd();

        $this->I->assertTrue($form->validate());
        $this->I->assertTrue($form->add());

        $usage = UsageVoip::findOne(['id' => $form->id]);
        return $usage;
    }

    /**
     * Проверка номера на наличие в продаже
     *
     * @param Number $number
     */
    function checkInStock(\app\models\Number $number)
    {
        $this->I->assertEquals($number->status, Number::STATUS_INSTOCK);
        $this->I->assertNull($number->client_id);
        $this->I->assertNull($number->reserve_from);
        $this->I->assertNull($number->reserve_till);
        $this->I->assertNull($number->hold_to);
    }

    /**
     * Проверка номера в отстойнике
     *
     * @param Number $number
     */
    function checkHold(\app\models\Number $number)
    {
        $this->I->assertEquals($number->status, Number::STATUS_NOTACTIVE_HOLD);
        $this->I->assertNotNull($number->hold_from);
        $this->I->assertNotNull($number->hold_to);

        $dtHoldFrom = new \DateTime($number->hold_from, new \DateTimeZone('UTC'));
        $this->I->assertNotNull($dtHoldFrom);

        $dtHoldTo = new \DateTime($number->hold_to, new \DateTimeZone('UTC'));
        $this->I->assertNotNull($dtHoldTo);

        $diff = $dtHoldFrom->diff($dtHoldTo);
        $this->I->assertNotNull($diff);
        $this->I->assertEquals($diff->m, 6); // 6 month
    }
}