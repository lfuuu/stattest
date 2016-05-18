<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


/**
 * Class MinDayLimitNotificationProcessor
 * @package app\classes\notification\processors
 */
class MinDayLimitNotificationProcessor extends NotificationProcessor
{
    public function getEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT;
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