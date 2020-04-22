<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\modules\nnp\models\NdcType;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class VoipServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageVoip */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageVoip::class;
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_VOIP;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVoip[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        $result = [];

        $services = UsageVoip::find()
            ->client($clientAccount->client)
            ->actual()
            ->andWhere(['next_usage_id' => 0])
            ->orderBy(['id' => SORT_DESC]);


        foreach ($services->each() as $service) {
            // Skip line7800 without number
            if (
                $service->ndc_type_id === NdcType::ID_FREEPHONE
                && UsageVoip::find()->where(['line7800_id' => $service->id])->count()
            ) {
                continue;
            }

            $result[] = $service;
        }

        return $result;
    }

    /**
     * @param int $serviceId
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setServiceById($serviceId)
    {
        $this->_service = UsageVoip::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageVoip #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageVoip
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageVoip $service
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
            'voip_number' => $this->getService()->helper->value,
            'city_id' => $this->getService()->voipNumber ? $this->getService()->voipNumber->getCityByNumber()->id : null,
        ];
    }

    /**
     * @return AccountTariffResourceLog[]
     */
    public function getResources()
    {
        $lineResource = new AccountTariffResourceLog;
        $lineResource->setAttributes([
            'amount' => $this->getService()->no_of_lines,
            'resource_id' => ResourceModel::ID_VOIP_LINE,
        ]);

        $fmcResource = new AccountTariffResourceLog;
        $fmcResource->setAttributes([
            'amount' => 0,
            'resource_id' => ResourceModel::ID_VOIP_FMC,
        ]);

        $mobileOutboudResource = new AccountTariffResourceLog;
        $mobileOutboudResource->setAttributes([
            'amount' => 0,
            'resource_id' => ResourceModel::ID_VOIP_MOBILE_OUTBOUND,
        ]);

        return [
            $lineResource,
            $fmcResource,
            $mobileOutboudResource,
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

        // Try to process line7800
        $this->_line7800Process($preProcessor);

        // Try to process packages
        $this->_packagesProcess($preProcessor);
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
    private function _line7800Process(PreProcessor $preProcessor)
    {
        $service = $this->getService();

        if ($service->line7800) {
            $transferResult = $preProcessor->processor->run(
                (new PreProcessor)
                    ->setProcessor(new $preProcessor->processor)
                    ->setServiceType(Processor::SERVICE_VOIP)
                    ->setService(
                        $preProcessor->processor->getHandler(Processor::SERVICE_VOIP),
                        $this->getService()->line7800_id
                    )
                    ->setProcessedFromDate($preProcessor->activationDate)
                    ->setSourceClientAccount($preProcessor->clientAccount->id)
                    ->setTargetClientAccount($preProcessor->targetClientAccount->id)
            );

            $service->line7800_id = $transferResult->targetServiceHandler->getService()->primaryKey;

            if (!$service->save()) {
                throw new ModelValidationException($service);
            }
        }
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
    private function _packagesProcess(PreProcessor $preProcessor)
    {
        $packages = UsageVoipPackage::find()
            ->andWhere(['usage_voip_id' => $this->getService()->prev_usage_id])
            ->andWhere(['<=', 'actual_from', $preProcessor->expireDate])
            ->andWhere(['>=', 'actual_to', $preProcessor->expireDate]);

        if ($packages->count()) {
            foreach ($packages->each() as $package) {
                $preProcessor->processor->run(
                    (new PreProcessor)
                        ->setProcessor(new $preProcessor->processor)
                        ->setServiceType(Processor::SERVICE_PACKAGE)
                        ->setService(
                            $preProcessor->processor->getHandler(Processor::SERVICE_PACKAGE),
                            $package->id
                        )
                        ->setProcessedFromDate($preProcessor->activationDate)
                        ->setSourceClientAccount($preProcessor->clientAccount->id)
                        ->setTargetClientAccount($preProcessor->targetClientAccount->id)
                        ->setRelation('usage_voip_id', $preProcessor->targetServiceHandler->getService()->primaryKey)
                );
            }
        }
    }

}