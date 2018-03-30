<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\PartnerRewards;

abstract class MarginPercentageReward implements Reward
{

    const REWARD_FIELD = 'percentage_of_margin';

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
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings)
    {
        if (!array_key_exists(self::getField(), $settings)) {
            return false;
        }

        $percentage = $settings[self::getField()]; // сколько процентов прибыли отдать партнеру
        $margin = $line->cost_price ?
            max(0, $line->price * $line->amount - $line->cost_price) : // маржа = разница между стоимостью и себестоимостью
            0; // себестоимость неизвестна
        $reward->percentage_of_margin = $percentage * $margin / 100;

        return true;
    }
}