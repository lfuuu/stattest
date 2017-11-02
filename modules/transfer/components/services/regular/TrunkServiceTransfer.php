<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\forms\services\decorators\TrunkRegularServiceDecorator;
use app\modules\uu\models\ServiceType;
use DateTime;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class TrunkServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageTrunk */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageTrunk::className();
    }

    /**
     * @param ActiveRecord $service
     * @return TrunkRegularServiceDecorator
     */
    public function getServiceDecorator($service)
    {
        return new TrunkRegularServiceDecorator(['service' => $service]);
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_TRUNK;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageTrunk[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageTrunk::find()
            ->andWhere(['client_account_id' => $clientAccount->id])
            ->andWhere(['<=', 'actual_from', (new DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT)])
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
        $this->_service = UsageTrunk::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageTrunk #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageTrunk
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageTrunk $service
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
        /** @var UsageTrunk $service */
        $service = $this->getService();

        return [
            'service_type_id' => $this->getServiceTypeId(),
            'region_id' => $service->connection_point_id,
            'comment' => $service->description,
        ];
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \Exception
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        parent::finalizeOpen($preProcessor);

        // Process settings
        $this->_settingsProcess($preProcessor);
    }

    private function _settingsProcess(PreProcessor $preProcessor)
    {
        /** @var UsageTrunk $service */
        $service = $preProcessor->sourceServiceHandler->getService();

        /** @var UsageTrunkSettings $setting */
        foreach ($service->settings as $setting) {
            $targetSetting = new UsageTrunkSettings;
            $targetSetting->setAttributes($setting->getAttributes(null, ['id']), $safeOnly = false);
            $targetSetting->usage_id = $preProcessor->targetServiceHandler->getService()->primaryKey;

            if (!$targetSetting->save()) {
                throw new ModelValidationException($targetSetting);
            }
        }
    }

}
