<?php

namespace app\helpers;

use Yii;
use DateTime;
use DateTimeZone;

class DateTimeZoneHelper extends \yii\helpers\FileHelper
{

    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    const TIMEZONE_DEFAULT = 'UTC';
    const TIMEZONE_MOSCOW = 'Europe/Moscow';

    public static function getDateTime($date, $format = self::DATETIME_FORMAT, $showTimezoneName = true)
    {
        if (!$date) {
            return;
        }
        $datetime = (new DateTime($date))->setTimezone(new DateTimeZone(self::getUserTimeZone()));
        if ($format !== false) {
            return $showTimezoneName ? $datetime->format($format) . ' (' . static::getTimezoneDescription() . ')' : $datetime->format($format);
        }
        return $datetime;
    }

    /**
     * @param string $date
     * @param DateTimeZone|string $timezone
     * @param string $format
     * @return string
     */
    public static function getExpireDateTime($date, $timezone, $format = self::DATETIME_FORMAT)
    {
        if (!($timezone instanceof DateTimeZone)) {
            $timezone = new DateTimeZone($timezone ?: self::TIMEZONE_DEFAULT);
        }

        return
            (new DateTime($date, $timezone))
                ->setTimezone(new DateTimeZone('UTC'))
                ->modify('+1 day -1 second')
                ->format($format);
    }

    public static function setDateTime($date, $format = false)
    {
        $datetime = new DateTime($date, new DateTimeZone(self::getUserTimeZone()));
        $datetime->setTimezone(new DateTimeZone('UTC'));
        return $format !== false ? $datetime->format($format) : $datetime;
    }

    private static function getTimezoneDescription()
    {
        $timezone = static::getUserTimeZone();
        if ($timezone == self::TIMEZONE_MOSCOW) {
            return 'Msk';
        }
        else if (strpos($timezone, '/') !== false) {
            list(, $region) = explode('/', $timezone);
            return substr(str_replace(['a', 'o', 'e', 'u', 'i', 'y'], '', $region), 0, 3);
        }
        else {
            return $timezone;
        }
    }

    private static function getUserTimeZone()
    {
        return isset(Yii::$app->user->identity) ? Yii::$app->user->identity->timezone_name : 'UTC';
    }

}
