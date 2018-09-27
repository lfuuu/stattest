<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\UsageIpPorts;
use app\models\UsageIpRoutes;
use app\models\UsageTechCpe;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class IpPortsServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageIpPorts */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageIpPorts::class;
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        // @todo Not supported by universal services
        return 0;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageIpPorts[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageIpPorts::find()
            ->client($clientAccount->client)
            ->actual()
            ->andWhere(['next_usage_id' => 0])
            ->all();
    }

    /**
     * @param int $serviceId
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setServiceById($serviceId)
    {
        $this->_service = UsageIpPorts::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageIpPorts #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageIpPorts
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageIpPorts $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * @return array
     */
    public function getBaseAttributes()
    {
        // @todo Not supported by universal services
        return [];
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws InvalidCallException
     * @throws \Exception
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        parent::finalizeOpen($preProcessor);

        // Process tariff
        $this->_tariffProcess($preProcessor);

        // Process routes
        $this->_processRoutes($preProcessor);

        // Process devices
        $this->_processDevices($preProcessor);
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws InvalidCallException
     * @throws \Exception
     */
    private function _processRoutes(PreProcessor $preProcessor)
    {
        $routes = UsageIpRoutes::find()
            ->andWhere(['port_id' => $preProcessor->sourceServiceHandler->getService()->primaryKey])
            ->andWhere(['<=', 'actual_from', $preProcessor->activationDate])
            ->andWhere(['>=', 'actual_to', $preProcessor->activationDate]);

        if ($routes->count()) {
            foreach ($routes->each() as $route) {
                $preProcessor->processor->run(
                    (new PreProcessor)
                        ->setProcessor(new $preProcessor->processor)
                        ->setServiceType(Processor::SERVICE_INTERNET_ROUTES)
                        ->setService(
                            $preProcessor->processor->getHandler(Processor::SERVICE_INTERNET_ROUTES),
                            $route->id
                        )
                        ->setProcessedFromDate($preProcessor->activationDate)
                        ->setSourceClientAccount($preProcessor->clientAccount->id)
                        ->setTargetClientAccount($preProcessor->targetClientAccount->id)
                        ->setRelation('port_id', $this->getService()->primaryKey)
                );
            }
        }
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws InvalidCallException
     * @throws \Exception
     */
    private function _processDevices(PreProcessor $preProcessor)
    {
        $devices = UsageTechCpe::find()
            ->andWhere(['service' => 'usage_ip_ports'])
            ->andWhere(['id_service' => $preProcessor->sourceServiceHandler->getService()->primaryKey])
            ->andWhere(['<=', 'actual_from', $preProcessor->activationDate])
            ->andWhere(['>=', 'actual_to', $preProcessor->activationDate]);

        if ($devices->count()) {
            foreach ($devices->each() as $device) {
                $preProcessor->processor->run(
                    (new PreProcessor)
                        ->setProcessor(new $preProcessor->processor)
                        ->setServiceType(Processor::SERVICE_INTERNET_DEVICES)
                        ->setService(
                            $preProcessor->processor->getHandler(Processor::SERVICE_INTERNET_DEVICES),
                            $device->id
                        )
                        ->setProcessedFromDate($preProcessor->activationDate)
                        ->setSourceClientAccount($preProcessor->clientAccount->id)
                        ->setTargetClientAccount($preProcessor->targetClientAccount->id)
                        ->setRelation('id_service', $this->getService()->primaryKey)
                );
            }
        }
    }

}