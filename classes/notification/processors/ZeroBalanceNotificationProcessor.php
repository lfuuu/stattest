<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


class ZeroBalanceNotificationProcessor extends NotificationProcessor
{
    /**
     * @inheritdoc
     */
    public function filterClients()
    {
        $this->clients = $this->clients->prepayment();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEnterEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE;
    }

    /**
     * @inheritdoc
     */
    public function getLeaveEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_UNSET_ZERO_BALANCE;
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
        return -$this->client->credit;
    }

    /**
     * @inheritdoc
     */
    protected function comparisonLevel()
    {
        return self::COMPARISON_LEVEL_NO_EQUAL;
    }

    /**
     * @inheritdoc
     */
    protected function isOldNotification()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getContactsForSend()
    {
        return $this->client->getAllContacts()->andWhere([
            'is_official' => 1,
            'type' => 'email'
        ])->all();
    }
}