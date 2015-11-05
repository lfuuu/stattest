<?php

namespace app\helpers;

use Yii;
use DateTime;
use DateTimeZone;

class DateTimeZoneHelper extends \yii\helpers\FileHelper
{

    public static function getDateTime($date, $format = 'Y-m-d H:i:s', $showTimezoneName = true)
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
        if ($timezone == "Europe/Moscow") {
            return "Msk";
        } else
        if (strpos($timezone, "/") !== false) {

            list($zone, $region) = explode("/", $timezone);

            $region = str_replace(["a", "o", "e", "u", "i", "y"], "", $region);

            return substr($region, 0, 3);
        } else {
            return $timezone;
        }
    }

    private static function getUserTimeZone()
    {
        return isset(Yii::$app->user->identity) ? Yii::$app->user->identity->timezone_name : 'UTC';
    }

}
