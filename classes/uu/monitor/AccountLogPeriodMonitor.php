<?php

namespace app\classes\uu\monitor;

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\helpers\DateTimeZoneHelper;
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
     * Вернуть статистику за этот день
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $monthDateTime
     * @param int $day
     * @return int 0 - нет данных, 1 - есть, но не в проводке, 2 - есть, в проводке
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
                    ':date_from' => $monthDateTime->modify('first day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                    ':date_to' => $monthDateTime->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                ])
                ->all();
        }

        $date = sprintf('%s-%02d', $monthDateTime->format('Y-m'), $day);
        foreach (self::$logs as $log) {
            if ($log->date_from <= $date && $date <= $log->date_to) {
                return $log->account_entry_id ? 2 : 1;
            }
        }

        return 0;
    }
}
