<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageSms;
use app\modules\uu\models\ServiceType;

class SmsServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageSms */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageSms::className();
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_SMS;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageSms[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageSms::find()
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
        $this->_service = UsageSms::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageSms #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageSms
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageSms $service
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
            'comment' => $this->getService()->comment,
        ];
    }

}