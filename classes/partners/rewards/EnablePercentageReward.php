<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\ClientAccount;
use app\models\PartnerRewards;
use app\classes\uu\model\AccountLogSetup;

abstract class EnablePercentageReward
{

    const REWARD_FIELD = 'percentage_once_only';

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
        if (
            $line->bill->biller_version === ClientAccount::VERSION_BILLER_UNIVERSAL &&
            !PartnerRewards::find()
                ->where([
                    'bill_id' => $line->bill->id,
                    'line_pk' => $line->pk,
                ])
                ->count() &&
            isset($settings[self::getField()])
        ) {
            $setupSummary =
                AccountLogSetup::find()
                    ->select('SUM(price)')
                    ->where(['account_entry_id' => $line->uu_account_entry_id])
                    ->scalar();

            if ($setupSummary) {
                $reward->percentage_once = $settings[self::getField()] * $setupSummary / 100;
            }
        }
    }

}