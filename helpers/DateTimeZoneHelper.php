<?php

namespace app\helpers;

use Yii;
use DateTime;
use DateTimeZone;

class DateTimeZoneHelper extends \yii\helpers\FileHelper
{

    public static function getDateTime($date, $format = 'Y-m-d H:i:s')
    {
        $timezone = isset(Yii::$app->user->identity) ? Yii::$app->user->identity->timezone_name : 'UTC';
        $datetime = (new DateTime($date))->setTimezone(new DateTimeZone($timezone));
        return $format !== false ? $datetime->format($format) : $datetime;
    }

    public static function setDateTime($date, $format = false)
    {
        $timezone = isset(Yii::$app->user->identity) ? Yii::$app->user->identity->timezone_name : 'UTC';
        $datetime = new DateTime($date, new DateTimeZone($timezone));
        $datetime->setTimezone(new DateTimeZone('UTC'));
        return $format !== false ? $datetime->format($format) : $datetime;
    }

}