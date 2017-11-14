<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
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
        return EventQueue::find()
            ->where(['status' => EventQueue::STATUS_PLAN])
            ->andWhere([
                '<',
                'date',
                (new \DateTime())
                    ->modify('-2 minutes')
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ])
            ->count();
    }

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 20, 30];
    }
}