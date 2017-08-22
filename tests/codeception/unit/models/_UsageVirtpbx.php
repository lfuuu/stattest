<?php

namespace tests\codeception\unit\models;

use ActiveRecord\DateTime;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Region;
use app\models\TariffVirtpbx;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;
use app\models\User;
use app\modules\nnp\models\NdcType;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\models\TariffVoip;
use tests\codeception\unit\_TestCase;

class _UsageVirtpbx extends \app\models\UsageVirtpbx
{
    /**
     * @inheritdoc
     */
    public static function createUsage(_TestCase $testCase, _ClientAccount $account)
    {
        $usage = new self();
        $usage->client = $account->client;
        $usage->actual_from = (new DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT);
        $usage->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
        $usage->amount = 1;
        $usage->status = UsageInterface::STATUS_CONNECTING;
        $usage->region = Region::MOSCOW;

        if (!$usage->validate()) {
            $testCase->failOnValidationModel($usage);
        }
        $testCase->assertTrue($usage->save());

        $logTariff = new LogTarif();
        $logTariff->service = UsageVirtpbx::tableName();
        $logTariff->id_service = $usage->id;
        $logTariff->id_tarif = TariffVirtpbx::TEST_TARIFF_ID;
        $logTariff->ts = (new DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $logTariff->date_activation = (new DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT);
        $logTariff->id_user = User::SYSTEM_USER_ID;

        if (!$logTariff->validate()) {
            $testCase->failOnValidationModel($logTariff);
        }
        $testCase->assertTrue($logTariff->save());

        return $usage;
    }

    public function switchOff(_TestCase $testCase)
    {
        $this->actual_from = $this->actual_to = date(DateTimeZoneHelper::DATE_FORMAT, strtotime('yesterday'));
        $this->validateAndSave($testCase);
    }

    public function validateAndSave(_TestCase $testCase)
    {
        if (!$this->validate()) {
            $testCase->failOnValidationModel($this);
        }
        $testCase->assertTrue($this->save());
    }

    public function getLogTariffDirect()
    {
        return $this->hasOne(LogTarif::className(), ['id_service' => 'id'])
            ->andWhere(['service' => parent::tableName()])
            ->orderBy(['id' => SORT_DESC]);
    }
}
