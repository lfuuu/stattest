<?php

namespace app\classes\uu\monitor;

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use DateTimeImmutable;

/**
 * Мониторинг для AccountLogPeriod
 */
class AccountLogPeriodMonitor extends AccountLogPeriod implements AccountLogMonitorInterface
{
    /** @var int */
    protected static $accountTariffId = null;

    /** @var AccountLogPeriod[] */
    protected static $logs = null;

    /**
     * Вернуть лог за этот день или null, если его нет
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $monthDateTime
     * @param int $day
     * @return int|null
     */
    public static function getMonitor(AccountTariff $accountTariff, DateTimeImmutable $monthDateTime, $day)
    {
        if (self::$accountTariffId === null || self::$accountTariffId !== $accountTariff->id) {
            // по этому клиенту кэша нет - надо все сбросит и посчитать заново
            self::$accountTariffId = null;
            self::$logs = null;
        }

        if (self::$logs === null) {
            // надо кэш посчитать заново
            self::$accountTariffId = $accountTariff->id;

            self::$logs = $accountTariff
                ->getAccountLogPeriods()
                ->where('((date_from BETWEEN :date_from AND :date_to) OR (date_to BETWEEN :date_from AND :date_to))', [
                    ':date_from' => $monthDateTime->format('Y-m-d'),
                    ':date_to' => $monthDateTime->modify('+1 month -1 day')->format('Y-m-d'),
                ])
                ->all();
        }

        $date = sprintf('%s-%02d', $monthDateTime->format('Y-m'), $day);
        foreach (self::$logs as $log) {
            if ($log->date_from <= $date && $date <= $log->date_to) {
                return 1;
            }
        }

        return null;
    }
}
