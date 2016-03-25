<?php
namespace app\classes;

use Yii;
use DateTime;
use DateTimeZone;
use app\models\usages\UsageInterface;

class DateTimeWithUserTimezone extends DateTime
{
    const TIMEZONE_DEFAULT = 'UTC';
    const TIMEZONE_MOSCOW = 'Europe/Moscow';

    const INFINITY = '∞';

    /**
     * @param string $time
     * @param DateTimeZone $timezone
     * @return DateTimeWithUserTimezone
     * @link http://php.net/manual/en/datetime.construct.php
     */
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone); // дату обычно устанавливаем в UTC
        $this->setTimezone($this->getUserTimeZone()); // а потом для вывода используем зону юзера
    }

    /**
     * Вернуть TimeZone юзера строкой
     * @return string
     */
    public function getUserTimeZoneString()
    {
        return isset(Yii::$app->user->identity) ?
            Yii::$app->user->identity->timezone_name :
            self::TIMEZONE_MOSCOW;
    }

    /**
     * Вернуть TimeZone юзера
     * @return DateTimeZone
     */
    public function getUserTimeZone()
    {
        return new DateTimeZone($this->getUserTimeZoneString());
    }

    /**
     * Вернуть дату для БД в таймзоне UTC
     * @return string
     */
    public function getDbDate()
    {
        $userTimeZone = $this->getTimezone();
        $this->setTimezone(new DateTimeZone(self::TIMEZONE_DEFAULT)); // установить UTC
        $dbDate = $this->format(self::ATOM);
        $this->setTimezone($userTimeZone); // установить (вернуть) юзерскую таймзону
        return $dbDate;
    }

    /**
     * Определяем дата находится ли в "бесконесности"
     * @return bool
     */
    public function isInfinity()
    {
        return $this > (new DateTime)->modify('+20 years');
    }

    /**
     * Отображение даты в заданном формате или "бесконечности"
     * @param string $format
     * @return string
     */
    public function formatWithInfinity($format)
    {
        return $this->isInfinity() ? self::INFINITY : $this->format($format);
    }

}