<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\models\usages\UsageInterface;
use app\models\UsageTechCpe;
use app\modules\transfer\components\services\PreProcessor;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

class TechCpeServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageTechCpe */
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
        $this->_service = UsageTechCpe::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageTechCpe #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageTechCpe
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageTechCpe $service
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
     * @throws InvalidParamException
     */
    public function closeService(PreProcessor $preProcessor)
    {
        $this->getService()->actual_to = $preProcessor->expireDate;
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