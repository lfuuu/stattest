<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidParamException;

class PackageServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageVoipPackage */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageVoipPackage::className();
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_VOIP_PACKAGE;
    }

    /**
     * @param int $serviceId
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setServiceById($serviceId)
    {
        $this->_service = UsageVoipPackage::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageVoipPackage #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageVoipPackage
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageVoipPackage $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidParamException
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        $this->getService()->prev_usage_id = $preProcessor->sourceServiceHandler->getService()->primaryKey;

        if ($preProcessor->relation !== null) {
            $this->getService()->setAttribute($preProcessor->relation->field, $preProcessor->relation->value);
        }

        if (!$this->getService()->save()) {
            throw new ModelValidationException($this->getService());
        }
    }

}