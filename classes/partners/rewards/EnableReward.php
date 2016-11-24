<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\PartnerRewards;

abstract class EnableReward
{

    const REWARD_FIELD = 'once_only';

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
        if (isset($settings[self::getField()])) {
            if (
                !PartnerRewards::find()
                ->where([
                    'bill_id' => $line->bill->id,
                    'line_pk' => $line->pk,
                ])
                ->count()
            ) {
                $reward->once = $settings[self::getField()];
            }
        }
    }

}