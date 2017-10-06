<?php

namespace app\health;

use app\models\EventQueue;

/**
 * В очереди не должно быть ошибочных записей
 */
class MonitorQueueStopped extends Monitor
{
    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return EventQueue::find()->where(['status' => EventQueue::STATUS_STOP])->count();
    }
}