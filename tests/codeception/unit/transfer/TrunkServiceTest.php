<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Region;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;
use app\models\UsageVirtpbx;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use app\modules\transfer\components\services\regular\RegularTransfer;
use app\modules\transfer\components\services\universal\UniversalTransfer;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use DateTime;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class TrunkServiceTest extends _BaseService
{

    protected $entryPointCode = 'RU5-TEST';

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_TRUNK;
    }

    /**
     * Test transfer between two regular accounts
     * It's no use for trunks
     *
     * @return bool
     */
    public function testRegular2Regular()
    {
        return true;
    }

    /**
     * Test transfer between regular and universal accounts
     * It's no use for trunks
     *
     * @return bool
     */
    public function testRegular2Universal()
    {
        return true;
    }

    /**
     * Test transfer between two universal accounts
     * It's no use for trunks
     *
     * @return bool
     */
    public function testUniversal2Universal()
    {
        return true;
    }

    /**
     * Test transfer between two regular accounts (using full structure service)
     *
     * @throws ModelValidationException
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \yii\db\Exception
     */
    public function testRegularFull2RegularFull()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $sourceService = $this->_createReqularServiceFull($this->regularClientAccountFirst);
        $processor = new RegularTransfer;
        $preProcessor = (new PreProcessor)
            ->setProcessor($processor)
            ->setServiceType($this->getServiceTypeCode())
            ->setService($processor->getHandler($this->getServiceTypeCode()), $sourceService->id)
            ->setProcessedFromDate($this->possibleDate)
            ->setSourceClientAccount($sourceService->clientAccount->id)
            ->setTargetClientAccount($this->regularClientAccountSecond->id);

        $transferResult = $this->runProcessor($processor, $preProcessor);

        /** @var UsageTrunk $sourceService */
        $sourceService = $transferResult->sourceServiceHandler->getService();
        /** @var UsageTrunk $resultService */
        $resultService = $transferResult->targetServiceHandler->getService();

        $this->assertEquals(
            (new DateTime($sourceService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($resultService->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        $this->assertEquals($sourceService->id, $resultService->prev_usage_id, 'Source service marked as transferred correctly');
        $this->assertEquals($sourceService->next_usage_id, $resultService->id, 'Result service marked as transferred correctly');

        // Check settings
        /** @var UsageTrunkSettings $sourceSettings */
        $sourceSettings = UsageTrunkSettings::findAll(['usage_id' => $sourceService->id]);
        /** @var UsageTrunkSettings $resultSettings */
        $resultSettings = UsageTrunkSettings::findAll(['usage_id' => $resultService->id]);

        $this->assertEquals(count($sourceSettings), count($resultSettings), 'Source and target settings equals');

        foreach ($sourceSettings as $index => $setting) {
            $this->assertEquals(
                $setting->getAttributes(null, ['id', 'usage_id']),
                $resultSettings[$index]->getAttributes(null, ['id', 'usage_id']),
                'Settings is is equals'
            );
        }

        $transaction->rollBack();
    }

    /**
     * * Test transfer between regular and universal accounts (using full structure service)
     *
     * @throws ModelValidationException
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \yii\db\Exception
     */
    public function testRegularFull2Universal()
    {
        $transaction = Yii::$app->db->beginTransaction();

        /** @var UsageTrunk $sourceService */
        $sourceService = $this->_createReqularServiceFull($this->regularClientAccountFirst);
        $processor = new RegularTransfer;

        // Get universal tariff ID
        $tariffId = $this->getTariffForUniversalService($sourceService);

        $preProcessor = (new PreProcessor)
            ->setProcessor($processor)
            ->setServiceType($this->getServiceTypeCode())
            ->setService($processor->getHandler($this->getServiceTypeCode()), $sourceService->id)
            ->setProcessedFromDate($this->possibleDate)
            ->setSourceClientAccount($sourceService->clientAccount->id)
            ->setTargetClientAccount($this->universalClientAccountFirst->id)
            ->setTariff($tariffId);

        $transferResult = $this->runProcessor($processor, $preProcessor);

        /** @var UsageTrunk $sourceService */
        $sourceService = $transferResult->sourceServiceHandler->getService();
        /** @var AccountTariff $resultService */
        $resultService = $transferResult->targetServiceHandler->getService();

        $accountTariffLog = array_shift($resultService->accountTariffLogs);

        $this->assertEquals(
            (new DateTime($sourceService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($accountTariffLog->actual_from_utc))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        // Check regular trunk
        $targetReqularService = UsageTrunk::findOne(['id' => $resultService->id]);
        $this->assertNotNull($targetReqularService, 'See regular trunk service');

        $this->assertEquals(
            (new DateTime($sourceService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($targetReqularService->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        // Check regular trunk settings
        /** @var UsageTrunkSettings $sourceSettings */
        $sourceSettings = UsageTrunkSettings::findAll(['usage_id' => $sourceService->id]);
        /** @var UsageTrunkSettings $resultSettings */
        $resultSettings = UsageTrunkSettings::findAll(['usage_id' => $targetReqularService->id]);

        $this->assertEquals(count($sourceSettings), count($resultSettings), 'Source and target settings equals');

        foreach ($sourceSettings as $index => $setting) {
            $this->assertEquals(
                $setting->getAttributes(null, ['id', 'usage_id']),
                $resultSettings[$index]->getAttributes(null, ['id', 'usage_id']),
                'Settings is is equals'
            );
        }

        $transaction->rollBack();
    }

    /**
     * Test transfer between two universal accounts (using full structure service)
     *
     * @throws ModelValidationException
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \yii\db\Exception
     */
    public function testUniversalFull2UniversalFull()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $sourceService = $this->_createUniversalServiceFull($this->universalClientAccountFirst);
        $processor = new UniversalTransfer;

        $preProcessor = (new PreProcessor)
            ->setProcessor($processor)
            ->setServiceType($this->getServiceTypeCode())
            ->setService($processor->getHandler($this->getServiceTypeCode()), $sourceService->id)
            ->setProcessedFromDate($this->possibleDate)
            ->setSourceClientAccount($sourceService->clientAccount->id)
            ->setTargetClientAccount($this->universalClientAccountSecond->id)
            ->setTariff($sourceService->tariff_period_id);

        $transferResult = $this->runProcessor($processor, $preProcessor);

        /** @var AccountTariff $sourceService */
        $sourceService = $transferResult->sourceServiceHandler->getService();
        /** @var AccountTariff $resultService */
        $resultService = $transferResult->targetServiceHandler->getService();

        $sourceAccountTariffLog = array_shift($sourceService->accountTariffLogs);
        $resultAccountTariffLog = array_shift($resultService->accountTariffLogs);

        $this->assertEquals($resultService->prev_usage_id, $sourceService->id, 'Result service marked as transferred correctly');
        $this->assertNull($sourceAccountTariffLog->tariff_period_id, 'Source service tariff is closed');
        $this->assertNotNull($resultAccountTariffLog->tariff_period_id, 'Result service tariff is opened');

        // Check packages
        $packages = AccountTariff::findAll(['prev_account_tariff_id' => $resultService->id]);

        $this->assertEquals(1, count($packages));

        // Check regular trunk service
        /** @var UsageTrunk $sourceRegularService */
        $sourceRegularService = UsageTrunk::findOne(['id' => $sourceService->id]);
        $this->assertNotNull($sourceRegularService, 'See source regular trunk service');

        /** @var UsageTrunk $targetReqularService */
        $targetReqularService = UsageTrunk::findOne(['id' => $resultService->id]);
        $this->assertNotNull($targetReqularService, 'See target regular trunk service');

        $this->assertEquals(
            (new DateTime($sourceRegularService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($targetReqularService->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        // Check regular trunk settings
        /** @var UsageTrunkSettings $sourceSettings */
        $sourceSettings = UsageTrunkSettings::findAll(['usage_id' => $sourceService->id]);
        /** @var UsageTrunkSettings $resultSettings */
        $resultSettings = UsageTrunkSettings::findAll(['usage_id' => $targetReqularService->id]);

        $this->assertEquals(count($sourceSettings), count($resultSettings), 'Source and target settings equals');

        foreach ($sourceSettings as $index => $setting) {
            $this->assertEquals(
                $setting->getAttributes(null, ['id', 'usage_id']),
                $resultSettings[$index]->getAttributes(null, ['id', 'usage_id']),
                'Settings is is equals'
            );
        }

        $transaction->rollBack();
    }

    /**
     * @param ClientAccount $clientAccount
     * @param int|null $primaryKey
     * @return UsageVirtpbx
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount, $primaryKey = null)
    {
        // Creating service (Danger to use setAttributes method. Model can have missing rules)
        $service = new UsageTrunk;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client_account_id = $clientAccount->id;
        $service->connection_point_id = Region::MOSCOW;
        $service->trunk_id = 0;
        $service->operator_id = 0;

        if (!is_null($primaryKey)) {
            $service->id = $primaryKey;
        }

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     */
    protected function createUniversalService(ClientAccount $clientAccount)
    {
        return $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_TRUNK, $this->getTariffForUniversalService());
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return $this->getUniversalTariff(ServiceType::ID_TRUNK);
    }

    /**
     * @param ClientAccount $clientAccount
     * @param int|null $primaryKey
     * @return UsageTrunk
     * @throws ModelValidationException
     */
    private function _createReqularServiceFull(ClientAccount $clientAccount, $primaryKey = null)
    {
        // Create base service
        $service = $this->createRegularService($clientAccount, $primaryKey);

        $settings = [
            [
                'usage_id' => $service->id,
                'type' => UsageTrunkSettings::TYPE_TERMINATION,
                'order' => 1,
                'pricelist_id' => 114,
            ],
            [
                'usage_id' => $service->id,
                'type' => UsageTrunkSettings::TYPE_DESTINATION,
                'order' => 1,
                'minimum_cost' => 330,
            ],
        ];

        foreach ($settings as $setting) {
            $serviceSettings = new UsageTrunkSettings;
            $serviceSettings->setAttributes($setting, $safeOnly = false);

            if (!$serviceSettings->save()) {
                throw new ModelValidationException($serviceSettings);
            }
        }

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     * @throws ModelValidationException
     */
    private function _createUniversalServiceFull(ClientAccount $clientAccount)
    {
        // Search tariff
        $tariffId = $this->getUniversalTariff(ServiceType::ID_TRUNK);

        $service = $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_TRUNK, $tariffId);

        // Create package
        $packageTariffId = $this->getUniversalTariff(ServiceType::ID_TRUNK_PACKAGE_ORIG, $service->city_id);
        $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_TRUNK_PACKAGE_ORIG, $packageTariffId, [
            'prev_account_tariff_id' => $service->id,
        ]);

        // Create regular trunk service
        $this->_createReqularServiceFull($clientAccount, $service->id);

        return $service;
    }

}