<?php

namespace app\classes\partners\handler;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\AccountTariff;

class VoipHandler extends AHandler
{
    /**
     * @return array
     */
    public function getAvailableRewards()
    {
        return [
            EnableReward::class,
            EnablePercentageReward::class,
            MonthlyFeePercentageReward::class,
            ResourcePercentageReward::class,
            MarginPercentageReward::class,
        ];
    }

    /**
     * @param int $serviceId
     * @return UsageVoip|AccountTariff
     * @throws \yii\base\Exception
     */
    public function getService($serviceId)
    {
        $service = ($this->clientAccountVersion == ClientAccount::VERSION_BILLER_USAGE) ?
            UsageVoip::findOne(['id' => $serviceId]) :
            AccountTariff::findOne(['id' => $serviceId]);

        Assert::isObject($service);

        return $service;

    }

    /**
     * @param ActiveRecord $service
     * @return bool
     */
    public function isExcludeService($service)
    {
        if ($this->clientAccountVersion == ClientAccount::VERSION_BILLER_USAGE) {
            /** @var UsageVoip $service */
            $ndcTypeId = $service->ndc_type_id;
        } else {
            /** @var AccountTariff $service */
            $number = $service->number;
            $ndcTypeId = $number ? $number->ndc_type_id : null;
        }

        return $ndcTypeId == NdcType::ID_FREEPHONE;
    }
}