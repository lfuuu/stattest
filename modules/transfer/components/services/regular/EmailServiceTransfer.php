<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\models\UsageEmails;
use app\models\usages\UsageInterface;
use app\modules\transfer\components\services\PreProcessor;
use yii\base\InvalidParamException;

class EmailServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageEmails */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageEmails::class;
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
     * @return UsageEmails[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageEmails::find()
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
        $this->_service = UsageEmails::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageEmails #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageEmails
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageEmails $service
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
        /** @var UsageInterface|ActiveRecord $service */
        $service = $this->getService();

        $service->actual_to = $preProcessor->expireDate;
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
        $targetServiceDecorator = $this->getServiceDecorator($targetService);

        $targetService->actual_from = $preProcessor->activationDate;
        $targetService->{$targetServiceDecorator->getClientAccountUIDField()} = $targetServiceDecorator->getClientAccountUID($preProcessor->targetClientAccount);

        $this->setService($targetService);
    }

}