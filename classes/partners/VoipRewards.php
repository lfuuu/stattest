<?php

namespace app\classes\partners;

use app\classes\partners\rewards\EnableReward;
use app\classes\partners\rewards\EnablePercentageReward;
use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\MonthlyFeePercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;

abstract class VoipRewards
{

    public static
        $availableRewards = [
            EnableReward::class,
            EnablePercentageReward::class,
            MonthlyFeePercentageReward::class,
            ResourcePercentageReward::class,
            MarginPercentageReward::class,
        ];

}