<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;

/**
 * В очереди не должно быть ошибочных записей
 */
class MonitorQueue extends Monitor
{
    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 50, 100];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return EventQueue::find()
            ->where([
                'AND',
                ['status' => EventQueue::STATUS_PLAN],
                [
                    '<=',
                    'next_start',
                    (new \DateTime())
                        ->modify('-2 minutes')
                        ->format(DateTimeZoneHelper::DATETIME_FORMAT)
                ]
            ])
            ->orWhere(['status' => [EventQueue::STATUS_ERROR, EventQueue::STATUS_STOP]])
            ->count();
    }
}