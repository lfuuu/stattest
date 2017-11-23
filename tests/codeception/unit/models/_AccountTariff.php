<?php

namespace tests\codeception\unit\models;

use app\helpers\DateTimeZoneHelper;
use app\models\City;
use app\models\Number;
use app\models\Region;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use tests\codeception\unit\_TestCase;
use yii\codeception\TestCase;

class _AccountTariff extends AccountTariff
{
    /**
     * @param _TestCase $testCase
     * @param $accountId
     * @param $number
     * @return AccountTariff
     */
    public static function createVoip(_TestCase $testCase, $accountId, $number)
    {
        $accountTariffData = [
            'client_account_id' => $accountId,
            'service_type_id' => ServiceType::ID_VOIP,
            'tariff_period_id' => TariffPeriod::TEST_VOIP_ID,
            'actual_from' => date(DateTimeZoneHelper::DATE_FORMAT),
            'city_id' => City::MOSCOW,
            'voip_number' => $number
        ];

        return self::_create($testCase, $accountTariffData);
    }

    public static function createVpbx(_TestCase $testCase, $accountId)
    {
        $accountTariffData = [
            'client_account_id' => $accountId,
            'service_type_id' => ServiceType::ID_VPBX,
            'tariff_period_id' => TariffPeriod::TEST_VPBX_ID,
            'actual_from' => date(DateTimeZoneHelper::DATE_FORMAT),
            'city_id' => City::MOSCOW,
            'region_id' => Region::MOSCOW,
        ];

        return self::_create($testCase, $accountTariffData);
    }

    private static function _create(_TestCase $testCase, $accountTariffData)
    {
        $accountTariff = new self();
        $accountTariff->setAttributes($accountTariffData);

        if (!$accountTariff->save()) {
            if ($accountTariff->hasErrors()) {
                $testCase->failOnValidationModel($accountTariff);
            } else {
                $testCase->fail('Save error');
            }
        }

        // записать в лог тарифа
        $accountTariffLog = new AccountTariffLog();
        $accountTariffLog->account_tariff_id = $accountTariff->id;
        $accountTariffLog->setAttributes($accountTariffData);
        if (!$accountTariffLog->actual_from_utc) {
            $accountTariffLog->actual_from = date(DateTimeZoneHelper::DATE_FORMAT);
        }

        if (!$accountTariffLog->validate()) {
            $testCase->failOnValidationModel($accountTariffLog);
        }
        $testCase->assertTrue($accountTariffLog->save());

        if ($accountTariffData['service_type_id'] == ServiceType::ID_VOIP && isset($accountTariffData['voip_number'])) {
            Number::dao()->actualizeStatusByE164($accountTariffData['voip_number']);
        }

        return $accountTariff;
    }

    public function switchOff(_TestCase $testCase)
    {
        $this->tariff_period_id = null;
        $this->validateAndSave($testCase);

        Number::dao()->actualizeStatusByE164($this->voip_number);
    }

    public function validateAndSave(_TestCase $testCase)
    {
        if (!$this->validate()) {
            $testCase->failOnValidationModel($this);
        }
        $testCase->assertTrue($this->save());
    }
}
