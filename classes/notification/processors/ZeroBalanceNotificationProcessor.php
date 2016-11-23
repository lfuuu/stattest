<?php

namespace app\classes\notification\processors;

use app\models\important_events\ImportantEventsNames;


class ZeroBalanceNotificationProcessor extends NotificationProcessor
{
    public function filterClients()
    {
        $this->clients = $this->clients->prepayment();

        return $this;
    }

    public function getSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE;
    }

    public function getUnSetEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_UNSET_ZERO_BALANCE;
    }

    public function getValue()
    {
        return $this->client->billingCountersFastMass->realtimeBalance;
    }

    public function getLimit()
    {
        return -$this->client->credit;
    }

    protected function comparisonLevel()
    {
        return self::COMPARISON_LEVEL_NO_EQUAL;
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