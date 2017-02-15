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
    /**
     * @inheritdoc
     */
    public function filterClients()
    {
        $this->clients->andWhere(['not', ['voip_limit_mn_day' => 0]]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEnterEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT_MN;
    }

    /**
     * @inheritdoc
     */
    public function getLeaveEvent()
    {
        return ImportantEventsNames::IMPORTANT_EVENT_UNSET_DAY_LIMIT_MN;
    }

    /**
     * @inheritdoc
     */

    public function getValue()
    {
        return $this->client->billingCountersFastMass->dayMnSummary;
    }

    /**
     * @inheritdoc
     */

    public function getLimit()
    {
        return -$this->client->voip_limit_mn_day;
    }

    /**
     * @inheritdoc
     */
    protected function checkLimitToSkip($limit)
    {
        return $limit == 0;
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