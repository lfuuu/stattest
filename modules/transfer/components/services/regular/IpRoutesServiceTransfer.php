<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\UsageIpRoutes;
use app\models\usages\UsageInterface;
use app\modules\transfer\components\services\PreProcessor;
use yii\base\InvalidParamException;

class IpRoutesServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageIpRoutes */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        // Embedded service, part of IpPorts
        return '';
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
     * @param int $serviceId
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setServiceById($serviceId)
    {
        $this->_service = UsageIpRoutes::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageIpRoutes #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageIpRoutes
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageIpRoutes $service
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
     */
    public function openService(PreProcessor $preProcessor)
    {
        $sourceServiceHandler = $preProcessor->sourceServiceHandler;
        $sourceService = $sourceServiceHandler->getService();

        /** @var UsageInterface|ActiveRecord $targetService */
        $targetService = new $sourceService;
        $targetService->setAttributes($preProcessor->sourceServiceHandler->getAttributes(), $safeOnly = false);

        $targetService->activation_dt = $preProcessor->activationDatetime;
        $targetService->actual_from = $preProcessor->activationDate;

        $this->setService($targetService);
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidParamException
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        if ($preProcessor->relation !== null) {
            $this->getService()->setAttribute($preProcessor->relation->field, $preProcessor->relation->value);

            if (!$this->getService()->save()) {
                throw new ModelValidationException($this->getService());
            }
        }
    }

    /**
     * @param PreProcessor $preProcessor
     */
    public function finalizeClose(PreProcessor $preProcessor)
    {
    }

}