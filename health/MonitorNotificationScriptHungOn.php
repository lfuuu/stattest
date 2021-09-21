<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\models\Param;

/**
 * Мониторинг скрипта lk/check-notification
 */
class MonitorNotificationScriptHungOn extends Monitor
{
    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 1, 1];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $notificationParam = Param::getParam(Param::NOTIFICATIONS_SCRIPT_ON, Param::IS_OFF);
        if (!$notificationParam) {
            return Param::IS_OFF;
        }

        $notificationTime = new \DateTime($notificationParam);
        $now = new \DateTime('now');
        $anHourLess = $now->modify('-1 hour');

        if ($notificationTime < $anHourLess) {
            return Param::IS_ON;
        }

        return 0;
    }
}