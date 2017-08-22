<?php

namespace tests\codeception\unit\models;

use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\City;
use app\models\ClientAccount;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use tests\codeception\unit\_TestCase;

class _UsageVoip extends \app\models\UsageVoip
{
    const TARIFF_PUBLIC_ID = 531;
    const TARIFF_TEST_ID = 624;

    /**
     * @inheritdoc
     */
    public static function createUsage(_TestCase $testCase, ClientAccount $account, Number $number = null)
    {
        if (!$number) {
            $number = self::getFreeNumber();
        }


        $form = new UsageVoipEditForm();
        $form->scenario = 'add';
        $form->timezone = $account->timezone_name;
        $form->initModel($account);
        $form->did = $number->number;
        $form->tariff_main_id = self::TARIFF_TEST_ID;
        $form->prepareAdd();

        if (!$form->validate()) {
            $testCase->fail(implode(' ', $form->getFirstErrors()));
        }

        $testCase->assertTrue($form->add());

        $usage = self::findOne(['id' => $form->id]);
        return $usage;
    }

    public function switchOff(_TestCase $testCase)
    {
        $this->actual_from = $this->actual_to = date(DateTimeZoneHelper::DATE_FORMAT, strtotime('yesterday'));
        if (!$this->validate()) {
            $testCase->failOnValidationModel($this);
        }
        $testCase->assertTrue($this->save());
    }

    /**
     * @return Number
     */
    public static function getFreeNumber()
    {
        return (new FreeNumberFilter())
            ->setNdcType(NdcType::ID_GEOGRAPHIC)
            ->setCity(City::MOSCOW)
            ->setCountry(Country::RUSSIA)
            ->setDidGroup(DidGroup::ID_MOSCOW_STANDART_499)
            ->randomOne();
    }
}
