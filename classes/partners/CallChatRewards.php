<?php

namespace app\classes\partners;

use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;

abstract class CallChatRewards
{

    public static
        $availableRewards = [
            EnableReward::class,
            EnablePercentageReward::class,
            MonthlyFeePercentageReward::class,
        ];

}