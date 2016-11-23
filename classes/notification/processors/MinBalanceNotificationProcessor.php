<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


/**
 * Class MinBalanceNotificationProcessor
 * @package app\classes\notification\processors
 */
class MinBalanceNotificationProcessor extends NotificationProcessor
{

    public function getSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE;
    }

    public function getUnSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_UNSET_MIN_BALANCE;
    }

    public function getValue()
    {
        return $this->client->billingCountersFastMass->realtimeBalance;
    }

    public function getLimit()
    {
        return $this->client->lkClientSettings->min_balance;
    }

}