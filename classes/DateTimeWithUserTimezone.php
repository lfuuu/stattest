<?php

namespace app\classes;

use app\helpers\DateTimeZoneHelper;
use DateTime;
use DateTimeZone;
use Yii;

class DateTimeWithUserTimezone extends DateTime
{
    /**
     * @param string $time
     * @param DateTimeZone $timezone
     * @return DateTimeWithUserTimezone
     * @link http://php.net/manual/en/datetime.construct.php
     */
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        if (!$timezone) {
            $timezone = new DateTimeZone('UTC');
        }
        parent::__construct($time, $timezone); // дату обычно устанавливаем в UTC
        $this->setTimezone($this->getUserTimeZone()); // а потом для вывода используем зону юзера
        Yii::$app->formatter->timeZone = $this->getUserTimeZone();
    }

    /**
     * Вернуть TimeZone юзера строкой
     *
     * @return string
     */
    public function getUserTimeZoneString()
    {
        return (isset(Yii::$app->user->identity) && Yii::$app->user->identity->timezone_name) ?
            Yii::$app->user->identity->timezone_name :
            DateTimeZoneHelper::TIMEZONE_MOSCOW;
    }

    /**
     * Вернуть TimeZone юзера
     *
     * @return DateTimeZone
     */
    public function getUserTimeZone()
    {
        return new DateTimeZone($this->getUserTimeZoneString());
    }

    /**
     * Вернуть дату для БД в таймзоне UTC
     *
     * @return string
     */
    public function getDbDate()
    {
        $userTimeZone = $this->getTimezone();
        $this->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)); // установить UTC
        $dbDate = $this->format(self::ATOM);
        $this->setTimezone($userTimeZone); // установить (вернуть) юзерскую таймзону
        return $dbDate;
    }

    /**
     * Определяем дата находится ли в "бесконечности"
     *
     * @return bool
     */
    public function isInfinity()
    {
        return $this > (new DateTime)->modify('+20 years');
    }

    /**
     * Отображение даты в заданном формате или "бесконечности"
     *
     * @param string $format
     * @return string
     */
    public function formatWithInfinity($format)
    {
        return $this->isInfinity() ? DateTimeZoneHelper::INFINITY : $this->format($format);
    }

    /**
     * Вернуть дату
     *
     * @param string $format "short", "medium", "long", or "full"
     * @return string
     */
    public function getDate($format = 'medium')
    {
        return Yii::$app->formatter->asDate($this, $format);
    }

    /**
     * Вернуть дату и время
     *
     * @param string $format "short", "medium", "long", or "full"
     * @return string
     */
    public function getDateTime($format = 'medium')
    {
        $tzName = $this->getTzName();
        return Yii::$app->formatter->asDatetime($this, $format) . ($tzName ? ' (' . $tzName . ')' : '');
    }

    public function getTzName()
    {
        $tz = Yii::$app->formatter->timeZone;
        if ($tz->getLocation()['comments']) {
            $tzNameAr = explode("+", $tz->getLocation()['comments']);
            $tzName = strtolower($tzNameAr[0]);
            $tzName[0] = strtoupper($tzName[0]);
            return substr($tzName, 0, 3);
        }

        $tzNameAr = explode("/", $tz->getName());
        if (!isset($tzNameAr[1])) {
            return $tzNameAr[0];
        }
        $tzName = str_replace(['a', 'e', 'i', 'o', 'u', 'y'], '', $tzNameAr[1]);

        return substr($tzName, 0, 3);
    }

    /**
     * Вернуть значение секунд в формате  минуты,сотая_доля_минуты
     *
     * @param int $value
     *
     * @return string|null - важно чтобы возвращался если $value == null,
     * потому что в гриде пустая строка и null отображаются по разному
     */
    public static function formatSecondsToMinutesAndSeconds($value)
    {
        return $value !== null ? str_pad(floor($value / 60), 2, '0', STR_PAD_LEFT) . ',' . str_pad(round($value % 60 / 60 * 100), 2, '0', STR_PAD_LEFT) : null;
    }

    /**
     * Вернуть значение секунд в формате дни часы:минуты:секунды
     *
     * @param int $value
     *
     * @return string|null - важно чтобы возвращался если $value == null,
     * потому что в гриде пустая строка и null отображаются по разному
     */
    public static function formatSecondsToDayAndHoursAndMinutesAndSeconds($value)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value >= 24 * 60 * 60) {
            $d = floor($value / (24 * 60 * 60));
        } else {
            $d = 0;
        }

        return ($d ? $d . 'd ' : '') . gmdate('H:i:s', $value - $d * 24 * 60 * 60);
    }

    /**
     * Вернуть значение секунд в детальном формате: 7699,58 (5d 08:19:35)
     *
     * @param int $value
     *
     * @return string|null - важно чтобы возвращался если $value == null,
     * потому что в гриде пустая строка и null отображаются по разному
     */
    public static function formatSecondsToDetailedView($value)
    {
        if (is_null($value)) {
            return null;
        }

        return sprintf(
            '%s (%s)',
            DateTimeWithUserTimezone::formatSecondsToMinutesAndSeconds($value),
            DateTimeWithUserTimezone::formatSecondsToDayAndHoursAndMinutesAndSeconds($value)
        );
    }

}