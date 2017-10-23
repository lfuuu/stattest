<?php

namespace app\health;

use app\models\EventQueue;

/**
 * В очереди не должно быть ошибочных записей
 */
class MonitorQueueStopped extends Monitor
{
    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [2, 10, 50]; // 1 - это нормально (он сейчас обрабатывается)
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return EventQueue::find()->where(['status' => [EventQueue::STATUS_ERROR, EventQueue::STATUS_STOP]])->count();
    }
}