<?php

namespace app\modules\transfer\components\services;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\modules\transfer\forms\services\decorators\ServiceDecoratorInterface;
use app\modules\uu\models\AccountTariffResourceLog;

abstract class ServiceTransfer
{

    /** @var ActiveRecord */
    private $_service;

    /**
     * @return int
     */
    abstract public function getServiceTypeId();

    /**
     * @param ActiveRecord $service
     * @return ServiceDecoratorInterface
     */
    abstract public function getServiceDecorator($service);

    /**
     * Get possible to transfer services
     *
     * @param ClientAccount $clientAccount
     * @return array
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return [];
    }

    /**
     * Get list of related resources (lines, packages etc)
     *
     * @return AccountTariffResourceLog[]
     */
    public function getResources()
    {
        return [];
    }

    /**
     * Set processed service
     *
     * @param int $serviceId
     */
    abstract public function setServiceById($serviceId);

    /**
     * Get processed service
     *
     * @return ActiveRecord
     */
    abstract public function getService();

    /**
     * Update existing processed service
     *
     * @param $service
     */
    abstract public function setService($service);

    /**
     * Get processed service attributes
     *
     * @return array
     */
    abstract public function getAttributes();

    /**
     * Get processed service base attributes (cross attributes between regular & universal)
     *
     * @return array
     */
    abstract public function getBaseAttributes();

    /**
     * Close source usage
     *
     * @param PreProcessor $preProcessor
     */
    abstract public function closeService(PreProcessor $preProcessor);

    /**
     * What to do after successful service close
     *
     * @param PreProcessor $preProcessor
     */
    abstract public function finalizeClose(PreProcessor $preProcessor);

    /**
     * Create new usage based on type
     *
     * @param PreProcessor $preProcessor
     */
    abstract public function openService(PreProcessor $preProcessor);

    /**
     * What to do after successful service open
     *
     * @param PreProcessor $preProcessor
     */
    abstract public function finalizeOpen(PreProcessor $preProcessor);

}