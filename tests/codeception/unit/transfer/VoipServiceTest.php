<?php

namespace tests\codeception\unit\transfer;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\City;
use app\models\ClientAccount;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\Region;
use app\models\TariffVoip;
use app\models\TariffVoipPackage;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\modules\nnp\models\NdcType;
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

class VoipServiceTest extends _BaseService
{

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_VOIP;
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

        /** @var UsageVoip $sourceService */
        $sourceService = $transferResult->sourceServiceHandler->getService();
        /** @var UsageVoip $resultService */
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

        if ($this->isRegularLogTariff) {
            $this->assertNotNull(
                $resultService->getLogTariff(UsageInterface::MIDDLE_DATE),
                'Result service has correct tariff'
            );
        }

        // Check resources
        /** @var UsageVoip $sourceLineService */
        $sourceLineService = UsageVoip::findOne(['id' => $sourceService->line7800_id]);
        $this->assertNotNull($sourceLineService, 'Line service is not set correctly');

        /** @var UsageVoip $resultLineService */
        $resultLineService = UsageVoip::findOne(['id' => $resultService->line7800_id]);
        $this->assertNotNull($resultLineService, 'Line service is not set correctly');

        $this->assertEquals(
            (new DateTime($sourceLineService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($resultLineService->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        $this->assertEquals($sourceLineService->id, $resultLineService->prev_usage_id, 'Source line service marked as transferred correctly');
        $this->assertEquals($sourceLineService->next_usage_id, $resultLineService->id, 'Result line service marked as transferred correctly');

        if ($this->isRegularLogTariff) {
            $this->assertNotNull(
                $resultLineService->getLogTariff(UsageInterface::MIDDLE_DATE),
                'Result line service has correct tariff'
            );
        }

        // Check packages
        $sourcePackages = UsageVoipPackage::findAll(['usage_voip_id' => $sourceService->id]);
        $resultPackages = UsageVoipPackage::findAll(['usage_voip_id' => $resultService->id]);

        $this->assertEquals(count($sourcePackages), count($resultPackages));

        $sourcePackage = array_pop($sourcePackages);
        $resultPackage = array_pop($resultPackages);

        $this->assertEquals(
            (new DateTime($sourcePackage->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($resultPackage->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        $this->assertEquals($sourcePackage->id, $resultPackage->prev_usage_id, 'Source package service marked as transferred correctly');
        $this->assertEquals($sourcePackage->next_usage_id, $resultPackage->id, 'Result package service marked as transferred correctly');

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

        /** @var UsageVoip $sourceService */
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

        /** @var UsageVoip $sourceService */
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

        // Check resources
        $resources = $resultService->resources;
        $resultResources = [];

        foreach ($resources as $resource) {
            $accountTariffResourceLog = $resultService->getAccountTariffResourceLogs($resource->id)->one();
            if ($accountTariffResourceLog === null) {
                continue;
            }
            $resultResources[] = $accountTariffResourceLog->getAttributes();
        }

        $this->assertEquals(2, count($resultResources));

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

        $transaction->rollBack();
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVoip
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        return $this->_createRegularServiceSimple($clientAccount, $this->_getFreeNumber());
    }

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     */
    protected function createUniversalService(ClientAccount $clientAccount)
    {
        // Search free number
        $freeNumber = $this->_getFreeNumber();
        // Search tariff
        $tariffId = $this->getUniversalTariff(ServiceType::ID_VOIP, $freeNumber->cityByNumber->id, $freeNumber->did_group_id);

        return $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_VOIP, $tariffId, [
            'voip_number' => $freeNumber->number,
            'city_id' => $freeNumber->cityByNumber->id,
        ]);
    }

    /**
     * @param Usageinterface $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        $this->assertNotNull($service, 'Missing VOIP regular service');

        return $this->getUniversalTariff(ServiceType::ID_VOIP, $service->voipNumber->cityByNumber->id, $service->voipNumber->did_group_id);
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVoip
     * @throws ModelValidationException
     */
    private function _createReqularServiceFull(ClientAccount $clientAccount)
    {
        // Create base service
        $service = $this->_createRegularServiceSimple($clientAccount, $this->_getFreeNumber(NdcType::ID_FREEPHONE, null));

        // Create line7800 (Can't use setAttributes method. Model has missing rules)
        $lineService = new UsageVoip;
        $lineService->actual_from = $this->getActivationDate();
        $lineService->actual_to = $this->getExpireDate();
        $lineService->client = $clientAccount->client;
        $lineService->ndc_type_id = NdcType::ID_MCN_LINE;
        $lineService->address = 'test address';
        $lineService->region = Region::MOSCOW;
        $lineService->create_params = '';

        if (!$lineService->save()) {
            throw new ModelValidationException($lineService);
        }

        $this->setRegularTariff($lineService, $service->logTariff->id);

        // Set relation
        $service->line7800_id = $lineService->id;

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        // Create package
        $packageTariffId = TariffVoipPackage::find()->select('MAX(id)')->scalar();

        if (!(int)$packageTariffId) {
            $this->fail('Unknown voip package tariff');
        }

        $packageService = new UsageVoipPackage;
        $packageService->client = $service->client;
        $packageService->actual_from = $service->actual_from;
        $packageService->actual_to = $service->actual_to;
        $packageService->tariff_id = $packageTariffId;
        $packageService->usage_voip_id = $service->id;
        $packageService->status = UsageInterface::STATUS_WORKING;

        if (!$packageService->save()) {
            throw new ModelValidationException($packageService);
        }

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @param \app\models\Number $freeNumber
     * @return UsageVoip
     * @throws ModelValidationException
     */
    private function _createRegularServiceSimple(ClientAccount $clientAccount, \app\models\Number $freeNumber)
    {
        // Select available tariff
        $tariffId = TariffVoip::find()->select('MAX(id)')->scalar();

        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageVoip;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client = $clientAccount->client;
        $service->ndc_type_id = NdcType::ID_GEOGRAPHIC;
        $service->E164 = $freeNumber->number;
        $service->address = 'test address';
        $service->region = Region::MOSCOW;
        $service->create_params = '';

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        $this->setRegularTariff($service, $tariffId);

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     * @throws ModelValidationException
     */
    private function _createUniversalServiceFull(ClientAccount $clientAccount)
    {
        // Search free number
        $freeNumber = $this->_getFreeNumber();
        // Search tariff
        $tariffId = $this->getUniversalTariff(ServiceType::ID_VOIP, $freeNumber->cityByNumber->id, $freeNumber->did_group_id);

        $service = $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_VOIP, $tariffId, [
            'voip_number' => $freeNumber->number,
            'city_id' => $freeNumber->cityByNumber->id,
        ]);

        // Create package
        $packageTariffId = $this->getUniversalTariff(ServiceType::ID_VOIP_PACKAGE, $service->city_id);
        $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_VOIP_PACKAGE, $packageTariffId, [
            'prev_account_tariff_id' => $service->id,
        ]);

        return $service;
    }

    /**
     * @param int $ndcType
     * @param int $didGroup
     * @return \app\models\Number
     * @throws \yii\base\Exception
     */
    private function _getFreeNumber($ndcType = NdcType::ID_GEOGRAPHIC, $didGroup = DidGroup::ID_MOSCOW_STANDART_499)
    {
        // Search free number
        $freeNumber = (new FreeNumberFilter)
            ->setIsService(false)
            ->setNdcType($ndcType)
            ->setCountry(Country::RUSSIA)
            ->setCity(City::MOSCOW);

        if ($didGroup !== null) {
            $freeNumber->setDidGroup($didGroup);
        }

        $freeNumber = $freeNumber->randomOne();

        Assert::isObject($freeNumber, 'Unknown free number');

        return $freeNumber;
    }

}