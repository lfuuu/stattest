<?php

namespace app\health;

use app\models\SyncPostgres;

/**
 * В очереди на синхронизацию в PostgreSQL не должно быть много записей
 */
class MonitorZSyncPostgres extends Monitor
{
    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return SyncPostgres::find()->count();
    }

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [5, 10, 20];
    }
}