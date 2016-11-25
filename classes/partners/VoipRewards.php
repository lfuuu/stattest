<?php

namespace app\classes\partners;

use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;

abstract class VoipRewards implements RewardsInterface
{

    public static
        $availableRewards = [
            EnableReward::class,
            EnablePercentageReward::class,
            MonthlyFeePercentageReward::class,
            ResourcePercentageReward::class,
            MarginPercentageReward::class,
        ];

    /**
     * @param int $usageId
     * @param int $accountVersion
     * @return null|UsageInterface
     */
    public static function getUsage($usageId, $accountVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        if ((int)$accountVersion === ClientAccount::VERSION_BILLER_USAGE) {
            return UsageVoip::findOne(['id' => $usageId]);
        }
        return null;
    }

}