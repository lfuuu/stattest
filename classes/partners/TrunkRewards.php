<?php

namespace app\classes\partners;

use app\classes\partners\rewards\MarginPercentageReward;
use app\classes\partners\rewards\ResourcePercentageReward;

abstract class TrunkRewards
{

    public static
        $availableRewards = [
            ResourcePercentageReward::class,
            MarginPercentageReward::class,
        ];

}