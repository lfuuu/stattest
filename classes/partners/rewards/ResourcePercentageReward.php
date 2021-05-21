<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\PartnerRewards;
use app\models\Transaction;

abstract class ResourcePercentageReward implements Reward
{

    const REWARD_FIELD = 'percentage_of_over';

    /**
     * @return string
     */
    public static function getField()
    {
        return self::REWARD_FIELD;
    }

    /**
     * @param PartnerRewards $reward
     * @param BillLine $line
     * @param array $settings
     * @return bool
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings, $serviceObj)
    {
        if (!array_key_exists(self::getField(), $settings)) {
            return false;
        }

        if($line->isResource() && !$line->isResourceCalls()) {
            $reward->percentage_of_over = $settings[self::getField()] * $line->sum / 100;
        }

        return true;
    }

}