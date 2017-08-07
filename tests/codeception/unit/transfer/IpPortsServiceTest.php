<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\TariffInternet;
use app\models\TechPort;
use app\models\UsageIpPorts;
use app\models\UsageIpRoutes;
use app\models\usages\UsageInterface;
use app\models\UsageTechCpe;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use app\modules\transfer\components\services\regular\RegularTransfer;
use app\tests\codeception\fixtures\TariffInternetFixture;
use app\tests\codeception\fixtures\TechPortsFixture;
use DateTime;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class IpPortsServiceTest extends _BaseService
{

    /**
     * Load fixtures
     *
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function load()
    {
        // Loading fixtures regular fixtures
        (new TariffInternetFixture)->load();
        (new TechPortsFixture)->load();

        parent::load();
    }

    /**
     * Unload fixtures
     */
    protected function unload()
    {
        (new TariffInternetFixture)->unload();
        (new TechPortsFixture)->unload();

        parent::unload();
    }

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_INTERNET;
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

        /** @var UsageIpPorts $sourceService */
        $sourceService = $transferResult->sourceServiceHandler->getService();
        /** @var UsageIpPorts $resultService */
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

        // Check routes
        /** @var UsageIpRoutes $sourceRouteService */
        $sourceRoutesService = UsageIpRoutes::findAll(['port_id' => $sourceService->id]);
        /** @var UsageIpRoutes $resultRouteService */
        $resultRoutesService = UsageIpRoutes::findAll(['port_id' => $resultService->id]);

        $this->assertEquals(count($sourceRoutesService), count($resultRoutesService));

        $sourceRoute = array_pop($sourceRoutesService);
        $resultRoute = array_pop($resultRoutesService);

        $this->assertEquals(
            (new DateTime($sourceRoute->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($resultRoute->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        // Check devices
        /** @var UsageTechCpe $sourceRouteService */
        $sourceDevicesService = UsageTechCpe::findAll([
            'service' => 'usage_ip_ports',
            'id_service' => $sourceService->id,
        ]);
        /** @var UsageTechCpe $resultRouteService */
        $resultDevicesService = UsageTechCpe::findAll([
            'service' => 'usage_ip_ports',
            'id_service' => $resultService->id,
        ]);

        $this->assertEquals(count($sourceDevicesService), count($resultDevicesService));

        $sourceDevice = array_pop($sourceDevicesService);
        $resultDevice = array_pop($resultDevicesService);

        $this->assertEquals(
            (new DateTime($sourceDevice->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($resultDevice->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        $transaction->rollBack();
    }

    /**
     * Test transfer between regular and universal accounts
     *
     * @return bool
     */
    public function testRegular2Universal()
    {
        try {
            parent::testRegular2Universal();
        } catch (InvalidValueException $e) {
            return true;
        }

        return false;
    }

    /**
     * Test transfer between two universal accounts
     *
     * @return bool
     */
    public function testUniversal2Universal()
    {
        try {
            parent::testUniversal2Universal();
        } catch (InvalidValueException $e) {
            return true;
        }

        return false;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return bool
     */
    protected function createUniversalService(ClientAccount $clientAccount)
    {
        return false;
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return 0;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVoip
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        return $this->_createRegularServiceSimple($clientAccount);
    }

    /**
     * Test transfer between two regular accounts
     * It's no use for internet
     *
     * @return bool
     */
    public function testRegular2Regular()
    {
        return true;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVoip
     * @throws ModelValidationException
     */
    private function _createReqularServiceFull(ClientAccount $clientAccount)
    {
        // Create base service
        $service = $this->_createRegularServiceSimple($clientAccount);

        // Create route (Can't use setAttributes method. Model has missing rules)
        $routeService = new UsageIpRoutes;
        $routeService->actual_from = $this->getActivationDate();
        $routeService->actual_to = $this->getExpireDate();
        $routeService->net = '10.255.0.0/26';
        $routeService->port_id = $service->id;

        if (!$routeService->save()) {
            throw new ModelValidationException($routeService);
        }

        // Create device (Can't use setAttributes method. Model has missing rules)
        $deviceService = new UsageTechCpe;
        $deviceService->actual_from = $service->actual_from;
        $deviceService->actual_to = $service->actual_to;
        $deviceService->client = $clientAccount->client;
        $deviceService->id_model = 0;
        $deviceService->service = 'usage_ip_ports';
        $deviceService->id_service = $service->id;

        if (!$deviceService->save()) {
            throw new ModelValidationException($deviceService);
        }

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVoip
     * @throws ModelValidationException
     */
    private function _createRegularServiceSimple(ClientAccount $clientAccount)
    {
        // Select available tariff
        $tariffId = TariffInternet::find()->select('MAX(id)')->scalar();

        // Select available port
        $portId = TechPort::find()->select('MAX(id)')->scalar();

        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageIpPorts;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client = $clientAccount->client;
        $service->port_id = $portId;
        $service->amount = 1;

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        $this->setRegularTariff($service, $tariffId);

        return $service;
    }

}