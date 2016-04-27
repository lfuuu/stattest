<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


/**
 * Class DayLimitNotificationProcessor
 * @package app\classes\notification\processors
 */
class DayLimitNotificationProcessor extends NotificationProcessor
{
    public function getEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT;
    }

    public function getValue()
    {
        return -$this->client->billingCounters->daySummary;
    }

    public function getLimit()
    {
        return $this->client->lkClientSettings->day_limit;
    }

    protected function isPositiveComparison()
    {
        return false;
    }

}