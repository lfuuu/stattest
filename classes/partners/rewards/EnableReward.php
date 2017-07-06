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
     * @return bool
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings)
    {
        if (!array_key_exists(self::getField(), $settings)) {
            return false;
        }

        $calculatedRewards = PartnerRewards::find()
            ->where([
                'bill_id' => $line->bill->id,
                'line_pk' => $line->pk,
            ])
            ->count();

        if (!$calculatedRewards) {
            // Если вознаграждение еще не рассчитано
            $reward->once = $settings[self::getField()];
        }

        return true;
    }

}