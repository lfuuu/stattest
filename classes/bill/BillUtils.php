<?php
namespace app\classes\bill;

use DateTime;

class BillUtils
{
    /**
     * @return DateTime[]
     */
    public static function prepareUsagePeriod(DateTime $date, DateTime $actualFrom, $periodType, $isAlign)
    {
        $currentFrom = clone $actualFrom;
        $currentTo = clone $actualFrom;

        $yearFrom  = (int)$date->format('Y');
        $yearTo    = $yearFrom;
        $monthFrom = (int)$date->format('m');
        $monthTo   = $monthFrom;

        $usageMonth = (int)$actualFrom->format('m');
        $usageDay   = (int)$actualFrom->format('d');

        if ($isAlign) {
            $dayFrom = $dayTo = 1;
            $offset = 0;
        } else {
            $dayFrom = $dayTo = $usageDay;
            $offset = $usageMonth - 1;
        }

        if ($periodType == Biller::PERIOD_YEAR) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                self::calcPeriod(12, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($periodType == Biller::PERIOD_6_MONTH) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                self::calcPeriod(6, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($periodType == Biller::PERIOD_3_MONTH) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                self::calcPeriod(3, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($periodType == Biller::PERIOD_MONTH) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                self::calcPeriod(1, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($periodType == Biller::PERIOD_ONCE) {
            $yearFrom = $yearTo = $actualFrom->format('Y');
            $monthFrom = $monthTo = $actualFrom->format('m');
            $dayFrom = $dayTo = $actualFrom->format('d');
            $currentTo->setTime(0, 0, 1);
        } else {
            return [null, null];
        }

        $currentFrom->setDate($yearFrom, $monthFrom, $dayFrom);
        $currentTo->setDate($yearTo, $monthTo, $dayTo);
        $currentTo->modify('-1 second');


        return [$currentFrom, $currentTo];
    }

    private static function calcPeriod($period, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        $monthFrom = (ceil(($monthFrom - $offset) / $period) - 1) * $period + 1 + $offset;
        $monthTo = (ceil(($monthTo - $offset) / $period)) * $period + 1 + $offset;

        if ($monthFrom > 12) {
            $monthFrom -= 12;
            $yearFrom++;
        } elseif ($monthFrom < 1) {
            $monthFrom += 12;
            $yearFrom--;
        }

        if ($monthTo > 12) {
            $monthTo -= 12;
            $yearTo++;
        } elseif ($monthTo < 1) {
            $monthTo += 12;
            $yearTo--;
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthFrom, $yearFrom);
        $dayFrom = $dayFrom > $daysInMonth ? $daysInMonth : $dayFrom;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthTo, $yearTo);
        $dayTo = $dayTo > $daysInMonth ? $daysInMonth : $dayTo;

        return [$yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo];
    }
}