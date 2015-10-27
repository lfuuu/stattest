<?php

namespace app\helpers;

use Yii;
use DateTime;
use DateTimeZone;

class DateTimeZoneHelper extends \yii\helpers\FileHelper
{

    public static function getDateTime($date, $format = 'd.m.Y H:i', $showTimezoneName = true)
    {
        $datetime = (new DateTime($date))->setTimezone(new DateTimeZone(self::getUserTimeZone()));
        if ($format !== false) {
            return $showTimezoneName ? $datetime->format($format) . ' (' . static::getTimezoneDescription() . ')' : $datetime->format($format);
        }
        return $datetime;
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
        switch ($timezone) {
            case 'Europe/Moscow':
                return 'Msk';
            default:
                return $timezone;
        }
    }

    private static function getUserTimeZone()
    {
        return isset(Yii::$app->user->identity) ? Yii::$app->user->identity->timezone_name : 'UTC';
    }

}