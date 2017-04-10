<?php

namespace app\modules\uu\monitor;

use app\modules\uu\models\AccountTariff;
use DateTimeImmutable;

/**
 * Интерфейс мониторинга
 */
interface AccountLogMonitorInterface
{
    /**
     * Вернуть лог за этот день или null, если его нет
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $monthDateTime
     * @param int $day
     * @return int|null
     */
    public static function getMonitor(AccountTariff $accountTariff, DateTimeImmutable $monthDateTime, $day);
}
