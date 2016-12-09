<?php

namespace app\classes\partners;

use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use app\models\UsageTrunk;

abstract class TrunkRewards implements RewardsInterface
{

    public static
        $availableRewards = [
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
            return UsageTrunk::findOne(['id' => $usageId]);
        }
        return null;
    }

}