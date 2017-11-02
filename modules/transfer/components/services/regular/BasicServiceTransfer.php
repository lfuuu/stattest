<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\usages\UsageInterface;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\ServiceTransfer;
use app\modules\transfer\forms\services\decorators\RegularServiceDecorator;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

abstract class BasicServiceTransfer extends ServiceTransfer
{

    /**
     * @return string
     */
    public abstract function getServiceModelName();

    /**
     * @param ActiveRecord $service
     * @return RegularServiceDecorator
     */
    public function getServiceDecorator($service)
    {
        return new RegularServiceDecorator(['service' => $service]);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $primaryKey = $this->getService()->primaryKey();

        $attributes = $this->getService()->getOldAttributes();
        unset($attributes[reset($primaryKey)]);

        return $attributes;
    }

    /**
     * @return array
     */
    public function getBaseAttributes()
    {
        return $this->getService()->getAttributes();
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws InvalidParamException
     */
    public function closeService(PreProcessor $preProcessor)
    {
        /** @var UsageInterface|ActiveRecord $service */
        $service = $this->getService();

        $service->expire_dt = $preProcessor->expireDatetime;
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

        $targetService->activation_dt = $preProcessor->activationDatetime;
        $targetService->actual_from = $preProcessor->activationDate;
        $targetService->{$targetServiceDecorator->getClientAccountUIDField()} = $targetServiceDecorator->getClientAccountUID($preProcessor->targetClientAccount);

        $this->setService($targetService);
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidParamException
     * @throws InvalidValueException
     */
    public function finalizeClose(PreProcessor $preProcessor)
    {
        $this->getService()->next_usage_id = $preProcessor->targetServiceHandler->getService()->primaryKey;

        if (!$this->getService()->save()) {
            throw new ModelValidationException($this->getService());
        }
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidValueException
     * @throws InvalidParamException
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        $this->getService()->prev_usage_id = $preProcessor->sourceServiceHandler->getService()->primaryKey;

        if (!$this->getService()->save()) {
            throw new ModelValidationException($this->getService());
        }
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidValueException
     */
    public function _tariffProcess(PreProcessor $preProcessor)
    {
        LogTarifTransfer::process(
            $preProcessor->sourceServiceHandler->getService(),
            $this->getService()->primaryKey,
            $preProcessor->activationDate
        );
    }

}