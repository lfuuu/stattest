<?php

namespace app\classes\partners;

use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;
use app\models\ClientAccount;
use app\models\UsageCallChat;
use app\models\usages\UsageInterface;

abstract class CallChatRewards implements RewardsInterface
{

    public static
        $availableRewards = [
            EnableReward::class,
            EnablePercentageReward::class,
            MonthlyFeePercentageReward::class,
        ];

    /**
     * @param int $usageId
     * @param int $accountVersion
     * @return null|UsageInterface
     */
    public static function getUsage($usageId, $accountVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        if ((int)$accountVersion === ClientAccount::VERSION_BILLER_USAGE) {
            return UsageCallChat::findOne(['id' => $usageId]);
        }
        return null;
    }

}