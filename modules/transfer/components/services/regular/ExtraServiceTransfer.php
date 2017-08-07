<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageExtra;
use app\modules\uu\models\ServiceType;

class ExtraServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageExtra */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageExtra::className();
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_EXTRA;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageExtra[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageExtra::find()
            ->innerJoinWith('tariff', false)
            ->client($clientAccount->client)
            ->actual()
            ->andWhere(['next_usage_id' => 0])
            ->andWhere(['tarifs_extra.status' => ['public', 'special', 'archive']])
            ->andWhere(['NOT IN', 'tarifs_extra.code', ['welltime', 'wellsystem']])
            ->all();
    }

    /**
     * @param int $serviceId
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setServiceById($serviceId)
    {
        $this->_service = UsageExtra::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageExtra #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageExtra
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageExtra $service
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

        if (!empty($service->param_value)) {
            $comment .= 'Parameters:' . $service->param_value . PHP_EOL;
        }

        $comment .= $service->comment;

        return [
            'service_type_id' => $this->getServiceTypeId(),
            'comment' => $comment,
        ];
    }

}