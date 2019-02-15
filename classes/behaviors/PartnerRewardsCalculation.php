<?php

namespace app\classes\behaviors;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\EventQueue as OwnEvent;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

class PartnerRewardsCalculation extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'calculateRewards',
            ActiveRecord::EVENT_AFTER_UPDATE => 'calculateRewards'
        ];
    }

    /**
     * @param Event $event
     * @return bool
     * @throws ModelValidationException
     */
    public function calculateRewards(Event $event)
    {
        /** @var Bill $bill */
        $bill = $event->sender;

        if (!$bill->is_payed) {
            return false;
        }

        $clientAccount = ClientAccount::findOne(['id' => $bill->client_id]);
        if (!$clientAccount) {
            return false;
        }

        $contract = $clientAccount->contract;
        $partnerContractId = $contract->partner_contract_id;
        if (!$partnerContractId) {
            return false;
        }

        OwnEvent::go(OwnEvent::PARTNER_REWARD, [
            'client_id' => $clientAccount->id,
            'bill_id' => $bill->id,
            'created_at' =>
                (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT),
        ]);

        return true;
    }

}