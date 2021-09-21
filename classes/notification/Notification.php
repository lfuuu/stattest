<?php

namespace app\classes\notification;

use yii\base\BaseObject;
use app\classes\notification\processors\NotificationProcessor;
use app\classes\notification\processors\ZeroBalanceNotificationProcessor;
use app\classes\notification\processors\MinBalanceNotificationProcessor;
use app\classes\notification\processors\MinDayLimitNotificationProcessor;
use app\classes\notification\processors\DayLimitNotificationProcessor;
use app\classes\notification\processors\DayMnLimitNotificationProcessor;

class Notification extends BaseObject
{
    /**
     * Проверка создания основных событий оповещений
     */
    public function checkForNotification()
    {
        try {
            foreach ($this->_getNotificationProcessors() as $processor) {
                $processor->filterClients()->checkAndMakeNotifications();
            }
        } catch (\Exception $e) {
            echo PHP_EOL . '[Error]' . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @return NotificationProcessor[]
     */
    private function _getNotificationProcessors()
    {
        return [
            new ZeroBalanceNotificationProcessor(),
            new MinBalanceNotificationProcessor(),
            new MinDayLimitNotificationProcessor(),
            new DayLimitNotificationProcessor(),
            new DayMnLimitNotificationProcessor(),
        ];
    }
}