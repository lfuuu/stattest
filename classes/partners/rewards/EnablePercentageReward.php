<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\ClientAccount;
use app\models\PartnerRewards;
use app\modules\uu\models\AccountLogSetup;

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
     * @return bool
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings)
    {
        if (!array_key_exists(self::getField(), $settings)) {
            return false;
        }

        switch ($line->bill->biller_version) {
            case ClientAccount::VERSION_BILLER_USAGE: {
                // @todo regular service (maybe never need)
                break;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                $calculatedRewards = PartnerRewards::find()
                    ->where([
                        'bill_id' => $line->bill->id,
                        'line_pk' => $line->pk,
                    ])
                    ->count();

                if (!$calculatedRewards) {
                    // Если вознаграждение еще не рассчитано
                    $setupSummary =
                        AccountLogSetup::find()
                            ->select('SUM(price)')
                            ->where(['account_entry_id' => $line->uu_account_entry_id])
                            ->scalar();

                    if ($setupSummary) {
                        $reward->percentage_once = $settings[self::getField()] * $setupSummary / 100;
                    }
                }
                break;
            }
        }

        return true;
    }

}