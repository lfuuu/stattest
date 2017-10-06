<?php

namespace app\health;

use app\models\EventQueue;

/**
 * В очереди не должно быть много необработанных записей
 */
class MonitorQueuePlanned extends Monitor
{
    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return EventQueue::find()->where(['status' => EventQueue::STATUS_PLAN])->count();
    }

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [10, 20, 30];
    }
}