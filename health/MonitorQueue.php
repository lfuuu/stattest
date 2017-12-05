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
            ->where(['status' => [EventQueue::STATUS_PLAN, EventQueue::STATUS_ERROR, EventQueue::STATUS_STOP]])
            ->andWhere([
                '<=',
                'next_start',
                (new \DateTime())
                    ->modify('-2 minutes')
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ])
            ->count();
    }
}