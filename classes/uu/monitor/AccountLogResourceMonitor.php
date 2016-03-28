<?php

namespace app\classes\uu\monitor;

use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use DateTimeImmutable;

/**
 * Мониторинг для AccountLogResource
 */
class AccountLogResourceMonitor extends AccountLogResource implements AccountLogMonitorInterface
{
    /** @var int */
    protected static $accountTariffId = null;

    /** @var [] */
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

            self::$logs =
                $accountTariff->getAccountLogResources()
                    ->select([
                        'date' => 'date',
                        'cnt' => 'COUNT(price)',
                    ])
                    ->where('date BETWEEN :date_from AND :date_to', [
                        ':date_from' => $monthDateTime->format('Y-m-d'),
                        ':date_to' => $monthDateTime->modify('+1 month -1 day')->format('Y-m-d'),
                    ])
                    ->groupBy('date')
                    ->indexBy('date')
                    ->asArray()
                    ->all();
        }

        $date = sprintf('%s-%02d', $monthDateTime->format('Y-m'), $day);
        if (isset(self::$logs[$date])) {
            return (int)self::$logs[$date]['cnt'];
        }

        return null;
    }
}
