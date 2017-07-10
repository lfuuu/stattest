<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


/**
 * Class MinDayLimitNotificationProcessor
 * @package app\classes\notification\processors
 */
class MinDayLimitNotificationProcessor extends NotificationProcessor
{
    /**
     * @inheritdoc
     */
    public function getEnterEvent()
    {
        return ImportantEventsNames::MIN_DAY_LIMIT;
    }

    /**
     * @inheritdoc
     */
    public function getLeaveEvent()
    {
        return ImportantEventsNames::UNSET_MIN_DAY_LIMIT;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return -$this->client->billingCountersFastMass->daySummary;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->client->lkClientSettings->{$this->getEnterEvent()};
    }

    /**
     * @inheritdoc
     */
    protected function isPositiveComparison()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isLocalSeviceNotification()
    {
        return false;
    }

}