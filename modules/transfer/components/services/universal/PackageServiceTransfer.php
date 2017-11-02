<?php

namespace app\modules\transfer\components\services\universal;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidParamException;

class PackageServiceTransfer extends VoipServiceTransfer
{

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_VOIP_PACKAGE_CALLS;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return array
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return [];
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidParamException
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        $this->getService()->setAttribute('prev_usage_id', $preProcessor->sourceServiceHandler->getService()->primaryKey);

        if ($preProcessor->relation !== null) {
            $this->getService()->setAttribute($preProcessor->relation->field, $preProcessor->relation->value);
        }

        if (!$this->getService()->save()) {
            throw new ModelValidationException($this->getService());
        }

        // Create account tariff log
        $accountTariffLog = new AccountTariffLog;
        $accountTariffLog->setAttributes([
            'account_tariff_id' => $this->getService()->primaryKey,
            'tariff_period_id' => $preProcessor->tariffId,
            'actual_from' => $preProcessor->activationDate,
        ]);

        if (!$accountTariffLog->save()) {
            throw new ModelValidationException($accountTariffLog);
        }
    }

}