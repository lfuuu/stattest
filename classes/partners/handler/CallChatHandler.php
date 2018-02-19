<?php

namespace app\classes\partners\handler;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;
use app\models\ClientAccount;
use app\models\UsageCallChat;
use app\modules\uu\models\AccountTariff;

class CallChatHandler extends AHandler
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
        ];
    }

    /**
     * @param int $serviceId
     * @return UsageCallChat|AccountTariff
     * @throws \yii\base\Exception
     */
    public function getService($serviceId)
    {
        $service = ($this->clientAccountVersion == ClientAccount::VERSION_BILLER_USAGE) ?
            UsageCallChat::findOne(['id' => $serviceId]) :
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