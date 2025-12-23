<?php

namespace app\helpers;

use app\models\usages\UsageInterface;
use DateTimeImmutable;
use Yii;
use DateTime;
use DateTimeZone;

class DateTimeZoneHelper extends \yii\helpers\FileHelper
{

    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const DATE_FORMAT_EUROPE = 'd-m-Y';
    const DATE_FORMAT_US_DOTTED = 'm.d.Y';
    const DATE_FORMAT_EUROPE_DOTTED = 'd.m.Y';
    const HUMAN_DATE_FORMAT = 'd MMM y';

    const ISO8601_WITHOUT_TIMEZONE = 'Y-m-d\TH:i:s';

    const TIMEZONE_UTC = 'UTC';
    const TIMEZONE_LONDON = 'Europe/London';
    const TIMEZONE_MOSCOW = 'Europe/Moscow';
    const TIMEZONE_DEFAULT = self::TIMEZONE_UTC;

    const INFINITY = '∞'; // &#8734;

    /**
     * @param string $date
     * @param string $format
     * @param bool|true $showTimezoneName
     * @return DateTime|string
     * @throws \Exception
     */
    public static function getDateTime($date, $format = self::DATETIME_FORMAT, $showTimezoneName = true)
    {
        if (!$date) {
            return null;
        }

        $datetime = (new DateTime($date))->setTimezone(new DateTimeZone(self::getUserTimeZone()));
        if ($format !== false) {
            return $showTimezoneName ?
                $datetime->format($format) . ' (' . static::getTimezoneDescription() . ')' :
                $datetime->format($format);
        }

        return $datetime;
    }

    /**
     * @param string $date
     * @param DateTimeZone|string $timezone
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function getExpireDateTime($date, $timezone, $format = self::DATETIME_FORMAT)
    {
        if (!($timezone instanceof DateTimeZone)) {
            $timezone = new DateTimeZone($timezone ?: self::TIMEZONE_DEFAULT);
        }

        return
            (new DateTime($date, $timezone))
                ->setTimezone(new DateTimeZone(self::TIMEZONE_DEFAULT))
                ->modify('+1 day -1 second')
                ->format($format);
    }

    /**
     * @param string $date
     * @param DateTimeZone|string $timezone
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function getActivationDateTime($date, $timezone, $format = self::DATETIME_FORMAT)
    {
        if (!($timezone instanceof DateTimeZone)) {
            $timezone = new DateTimeZone($timezone ?: self::TIMEZONE_DEFAULT);
        }

        return
            (new DateTime($date, $timezone))
                ->setTimezone(new DateTimeZone(self::TIMEZONE_DEFAULT))
                ->format($format);
    }

    public static function formatDurationHuman(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ч';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' мин';
        }
        if (!$hours && !$minutes) {
            $parts[] = max(1, $secs) . ' с';
        }

        return implode(' ', $parts);
    }

    public static function setDateTime($date, $format = false)
    {
        $datetime = new DateTime($date, new DateTimeZone(self::getUserTimeZone()));
        $datetime->setTimezone(new DateTimeZone(self::TIMEZONE_DEFAULT));
        return $format !== false ? $datetime->format($format) : $datetime;
    }

    /**
     * @param string $checkDate
     * @param string $showDate
     * @return DateTime|string
     * @throws \Exception
     */
    public static function getDateTimeLimit($checkDate, $showDate = '')
    {
        $date = (new DateTime($checkDate));

        return
            $date->format(DateTimeZoneHelper::DATE_FORMAT) == UsageInterface::MAX_POSSIBLE_DATE
            ||
            round(($date->getTimestamp() - (new DateTime('now'))->getTimestamp()) / 365 / 24 / pow(60, 2)) > 20
                ?
                self::INFINITY :
                self::getDateTime($showDate ?: $checkDate, DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @return string
     */
    public static function getUserTimeZone()
    {
        return isset(Yii::$app->user->identity) ? Yii::$app->user->identity->timezone_name : self::TIMEZONE_DEFAULT;
    }

    /**
     * @return string
     */
    private static function getTimezoneDescription()
    {
        $timezone = static::getUserTimeZone();
        if ($timezone == self::TIMEZONE_MOSCOW) {
            return 'Msk';
        } else {
            if (strpos($timezone, '/') !== false) {
                list(, $region) = explode('/', $timezone);
                return substr(str_replace(['a', 'o', 'e', 'u', 'i', 'y'], '', $region), 0, 3);
            } else {
                return $timezone;
            }
        }
    }

    /**
     * Вернуть DateTime в таймзоне UTC
     *
     * @param string $date в дефолтной таймзоне
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public static function getUtcDateTime($date = 'now')
    {
        return (new DateTimeImmutable($date))
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
    }

    /**
     * Дата на 1-е число месяца
     *
     * @param DateTimeImmutable $dateTime
     * @return bool
     */
    public static function isFirstDayOfMonth(DateTimeImmutable $dateTime)
    {
        $currentDay = (int)$dateTime->format('j');

        return $currentDay === 1;
    }

    /**
     * Дата на последнее число месяца
     *
     * @param DateTimeImmutable $dateTime
     * @return bool
     */
    public static function isLastDayOfMonth(DateTimeImmutable $dateTime)
    {
        $daysInMonth = (int)$dateTime->format('t');
        $currentDay = (int)$dateTime->format('j');

        return $currentDay === $daysInMonth;
    }
}