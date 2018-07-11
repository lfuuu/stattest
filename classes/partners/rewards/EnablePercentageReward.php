<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\ClientAccount;
use app\models\PartnerRewards;
use app\modules\uu\models\AccountEntry;
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
                $accountEntry = $line->accountEntry;
                if ($accountEntry && $accountEntry->type_id == AccountEntry::TYPE_ID_SETUP) {
                    $reward->percentage_once = $settings[self::getField()] * $accountEntry->price_with_vat / 100;
                }
                break;
            }
        }

        return true;
    }

}