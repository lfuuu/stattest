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
        $date = (new DateTime($date))->setTimezone(new DateTimeZone($timezone));
        return $date->format($format);
    }

}