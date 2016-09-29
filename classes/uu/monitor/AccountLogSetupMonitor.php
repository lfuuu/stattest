<?php

namespace app\classes\uu\monitor;

use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\helpers\DateTimeZoneHelper;
use DateTimeImmutable;

/**
 * Мониторинг для AccountLogSetup
 */
class AccountLogSetupMonitor extends AccountLogSetup implements AccountLogMonitorInterface
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
     * @return int 0 - нет данных, 1 - есть, но не в проводке, 2 - есть, в проводке
     */
    public static function getMonitor(AccountTariff $accountTariff, DateTimeImmutable $monthDateTime, $day)
    {
        if (self::$accountTariffId === null || self::$accountTariffId !== $accountTariff->id) {
            // по этому клиенту кэша нет - надо все сбросить и посчитать заново
            self::$accountTariffId = null;
            self::$logs = null;
        }

        if (self::$logs === null) {
            // надо кэш посчитать заново
            self::$accountTariffId = $accountTariff->id;

            self::$logs =
                $accountTariff->getAccountLogSetups()
                    ->select([
                        'date',
                        'account_entry_id',
                    ])
                    ->where('date BETWEEN :date_from AND :date_to', [
                        ':date_from' => $monthDateTime->modify('first day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                        ':date_to' => $monthDateTime->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                    ])
                    ->indexBy('date')
                    ->asArray()
                    ->all();
        }

        $date = sprintf('%s-%02d', $monthDateTime->format('Y-m'), $day);
        if (isset(self::$logs[$date])) {
            return self::$logs[$date]['account_entry_id'] ? 2 : 1;
        }

        return 0;
    }
}
