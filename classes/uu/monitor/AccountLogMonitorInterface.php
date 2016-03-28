<?php

namespace app\classes\uu\monitor;

use app\classes\uu\model\AccountTariff;
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
