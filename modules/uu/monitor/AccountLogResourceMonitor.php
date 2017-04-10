<?php

namespace app\modules\uu\monitor;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
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
     * Вернуть статистику за этот день
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $monthDateTime
     * @param int $day
     * @return int[] кол-во всего, кол-во в проводке
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
                        'cnt_entry' => 'COUNT(account_entry_id)',
                    ])
                    ->where('date BETWEEN :date_from AND :date_to', [
                        ':date_from' => $monthDateTime->modify('first day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                        ':date_to' => $monthDateTime->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                    ])
                    ->groupBy('date')
                    ->indexBy('date')
                    ->asArray()
                    ->all();
        }

        $date = sprintf('%s-%02d', $monthDateTime->format('Y-m'), $day);
        if (isset(self::$logs[$date])) {
            return [
                (int)self::$logs[$date]['cnt'],
                (int)self::$logs[$date]['cnt_entry'],
            ];
        }

        return [0, 0];
    }
}
