<?php

namespace app\classes\partners\handler;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\classes\partners\rewards\ResourcePercentageReward;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use app\modules\uu\models\AccountTariff;

class TrunkHandler extends AHandler
{
    /**
     * @return array
     */
    public function getAvailableRewards()
    {
        return [
            ResourcePercentageReward::class,
        ];
    }

    /**
     * @param int $serviceId
     * @return UsageTrunk|AccountTariff
     * @throws \yii\base\Exception
     */
    public function getService($serviceId)
    {
        $service = ($this->clientAccountVersion == ClientAccount::VERSION_BILLER_USAGE) ?
            UsageTrunk::findOne(['id' => $serviceId]) :
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
        return false;
    }
}