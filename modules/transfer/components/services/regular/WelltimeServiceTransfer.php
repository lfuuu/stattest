<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageWelltime;
use app\modules\uu\models\ServiceType;

class WelltimeServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageWelltime */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageWelltime::class;
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_WELLTIME_SAAS;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageWelltime[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageWelltime::find()
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
        $this->_service = UsageWelltime::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageWelltime #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageWelltime
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageWelltime $service
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
        $service = $this->getService();
        $comment = '';

        if (!empty($service->ip)) {
            $comment .= 'IP:' . $service->ip . PHP_EOL;
        }

        if (!empty($service->router)) {
            $comment .= 'Router:' . $service->router . PHP_EOL;
        }

        $comment .= $service->comment;

        return [
            'service_type_id' => $this->getServiceTypeId(),
            'comment' => $comment,
        ];
    }

}