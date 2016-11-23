<?php

namespace app\classes\notification\processors;

use app\models\ClientAccount;
use app\models\important_events\ImportantEventsNames;


/**
 * Class DayMnLimitNotificationProcessor
 * @package app\classes\notification\processors
 */
class DayMnLimitNotificationProcessor extends NotificationProcessor
{
    public function filterClients()
    {
        $this->clients->andWhere(['not', ['voip_limit_mn_day' => 0]]);

        return $this;
    }
    
    public function getSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT_MN;
    }

    public function getUnSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_UNSET_DAY_LIMIT_MN;
    }

    public function getValue()
    {
        return $this->client->billingCountersFastMass->dayMnSummary;
    }

    public function getLimit()
    {
        return -$this->client->voip_limit_mn_day;
    }

    protected function checkLimitToSkip($limit)
    {
        return $limit == 0;
    }

    protected function isOldNotification()
    {
        return false;
    }


    public function getContactsForSend()
    {
        return $this->client->getAllContacts()->andWhere([
            'is_official' => 1,
            'is_active' => 1,
            'type' => 'email'
        ])->all();
    }

}