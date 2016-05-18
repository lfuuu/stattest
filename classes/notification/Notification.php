<?php

namespace app\classes\notification;

use yii\base\Object;
use app\classes\notification\processors\NotificationProcessor;
use app\classes\notification\processors\ZeroBalanceNotificationProcessor;
use app\classes\notification\processors\MinBalanceNotificationProcessor;
use app\classes\notification\processors\DayLimitNotificationProcessor;
use app\classes\notification\processors\MinDayLimitNotificationProcessor;


class Notification extends Object
{
    public function checkForNotification()
    {
        foreach ($this->getNotificationProcessors() as $processor) {
            $processor->filterClients()->checkAndMakeNotifications();
        }
    }

    /**
     * @return NotificationProcessor[]
     */
    private function getNotificationProcessors()
    {
        return [
            new ZeroBalanceNotificationProcessor(),
            new MinBalanceNotificationProcessor(),
            new MinDayLimitNotificationProcessor(),
            new DayLimitNotificationProcessor(),
        ];
    }
}