<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


/**
 * Class DayLimitNotificationProcessor
 * @package app\classes\notification\processors
 */
class DayLimitNotificationProcessor extends NotificationProcessor
{
    public function filterClients()
    {
        $this->clients->andWhere(['not', ['voip_credit_limit_day' => 0]]);

        return $this;
    }
    
    public function getSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT;
    }

    public function getUnSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_UNSET_DAY_LIMIT;
    }

    public function getValue()
    {
        return $this->client->billingCountersFastMass->daySummary;
    }

    public function getLimit()
    {
        return -$this->client->voip_credit_limit_day;
    }

    protected function checkLimitToSkip($limit)
    {
        return $limit == 0;
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