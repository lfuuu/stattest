<?php

namespace app\classes\partners\rewards;

use app\models\Bill;
use app\models\BillLine;
use app\models\PartnerRewards;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

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
     * @param UsageVoip|UsageVirtpbx|AccountTariff $serviceObj
     * @return bool
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings, $serviceObj)
    {
        if (!array_key_exists(self::getField(), $settings)) {
            return false;
        }

        // разовые подключения только у усновных услуг
        /** @var AccountTariff $serviceObj */
        if ($serviceObj->id > AccountTariff::DELTA && $serviceObj->prev_account_tariff_id) {
            return false;
        }

        // разовое вознаграждение за услугу уже посчитано
        $isCalculatedRewards = PartnerRewards::find()
            ->where([
                'reward_service_type_id' => $reward->reward_service_type_id,
                'reward_service_id' => $reward->reward_service_id,
            ])
            ->exists();

        $reward->once = 0;

        if ($isCalculatedRewards) {
            return true;
        }

        $bill = $line->bill;

        // Есть ли эта услуга в более ранних оплачиных, а значит обсчитаных, счетах
        $isHavePrevPayedBills = BillLine::find()
            ->alias('l')
            ->joinWith('bill b')
            ->andWhere([
                'b.client_id' => $bill->client_id,
                'b.is_payed' => Bill::STATUS_IS_PAID,
                'l.service' => $line->service,
                'l.id_service' => $line->id_service,
            ])
            ->andWhere(['<=', 'payment_date', $bill->payment_date])
            ->andWhere(['not', ['b.bill_no' => $bill->bill_no]]);

        $isHavePrevPayedBills = $isHavePrevPayedBills->exists();

        if (!$isHavePrevPayedBills) {
            // Если вознаграждение еще не рассчитано
            $reward->once = $settings[self::getField()];
        }

        return true;
    }

}