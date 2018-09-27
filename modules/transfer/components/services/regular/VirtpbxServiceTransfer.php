<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\UsageVirtpbx;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class VirtpbxServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageVirtpbx */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageVirtpbx::class;
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_VPBX;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVirtpbx[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageVirtpbx::find()
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
        $this->_service = UsageVirtpbx::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageVirtpbx #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageVirtpbx
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageVirtpbx $service
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
        return [
            'service_type_id' => $this->getServiceTypeId(),
            'region_id' => $this->getService()->region,
            'comment' => $this->getService()->comment,
        ];
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
    }

}
