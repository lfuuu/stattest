<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


/**
 * Class MinBalanceNotificationProcessor
 * @package app\classes\notification\processors
 */
class MinBalanceNotificationProcessor extends NotificationProcessor
{
    /**
     * @inheritdoc
     */
    public function getEnterEvent()
    {
        return ImportantEventsNames::MIN_BALANCE;
    }

    /**
     * @inheritdoc
     */
    public function getLeaveEvent()
    {
        return ImportantEventsNames::UNSET_MIN_BALANCE;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->client->billingCountersFastMass->realtimeBalance;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return $this->client->lkClientSettings->min_balance;
    }

    /**
     * @inheritdoc
     */
    public function isLocalSeviceNotification()
    {
        return false;
    }

}