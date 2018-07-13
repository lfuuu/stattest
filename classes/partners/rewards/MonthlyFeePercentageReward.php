<?php

namespace app\classes\partners\rewards;

use app\dao\BillDao;
use app\models\BillLine;
use app\models\PartnerRewards;
use app\modules\uu\models\AccountEntry;

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
        if (!array_key_exists(self::getField(), $settings)) {
            return;
        }

        // Проверка на тип ресурса для УЛС через accountEntry и для ЛС через дату счета и дату выставления строчки счета
        if ($line->isResource()) {
            return;
        }

        if ($line->service == BillDao::UU_SERVICE) {
            // Проверяем, что тип строчки счета в проводках - Абонентская плата
            $accountEntry = $line->accountEntry;
            if ($accountEntry && $accountEntry->type_id == AccountEntry::TYPE_ID_PERIOD) {
                $reward->percentage_of_fee = $settings[self::getField()] * $line->sum / 100;
            }
        } else {
            $reward->percentage_of_fee = $settings[self::getField()] * $line->sum / 100;
        }
    }

}