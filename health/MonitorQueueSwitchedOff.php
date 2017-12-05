<?php

namespace app\health;

use app\models\EventQueue;

/**
 * В очереди не должно быть пропущенных записей, когда API выключен
 */
class MonitorQueueSwitchedOff extends Monitor
{
    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 10, 50];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return EventQueue::find()
            ->where(['log_error' => EventQueue::API_IS_SWITCHED_OFF])
            ->count();
    }
}