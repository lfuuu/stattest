<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\PartnerRewards;
use app\models\Transaction;

abstract class MonthlyFeePercentageReward implements Reward
{

    const REWARD_FIELD = 'percentage_of_fee';

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
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings)
    {
        if ($line->type !== Transaction::TYPE_RESOURCE && isset($settings[self::getField()])) {
            $reward->percentage_of_fee = $settings[self::getField()] * $line->sum / 100;
        }
    }

}