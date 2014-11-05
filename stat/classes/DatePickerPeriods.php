<?php

class DatePickerPeriods
{
	/**
	 * @var array $default_period - массив с данными о периодах (ключ массива - имя или часть имени переменной в Smarty, значение - Строка даты/времени)
	 */
	private static $default_period = array('cur_' => '0 month', 'today' => '0 days');
	/**
	 * @var string $format формат передачи в Smarty данных, аналогично первому параметру функции date() 
	 */
	public static $format = 'd-m-Y';
	/**
	 * Передает в Smarty значения периодов, отформатированных согласно свойству $format
	 *
	 * @param DateTime $date экземпляр класса DateTime
	 * @param  array $periods - массив с данными о периодах (ключ массива - имя или часть имени переменной в Smarty, значение - Строка даты/времени)
	 */
	public static function assignPeriods(DateTime $date, $periods = array())
	{
		global $design;
		if (empty($periods))
		{
			$periods = self::$default_period;
		}
		foreach ($periods as $prefix => $v) 
		{
			if (strpos($v, 'month') === false) 
			{
				self::assignOneDay($date, $prefix, $v);
			} else {
				self::assignStartEndMonth($date, $prefix, $v);
			} 
		}
	}
	/**
	 * Передает в Smarty значения, отформатированные согласно свойству $format, начала и конца месяца даты, полученной в результате смещения даты $date на $interval
	 *
	 * @param DateTime $date экземпляр класса DateTime
	 * @param string $prefix - часть имени переменной в Smarty
	 * @param string $interval - Строка даты/времени
	 */
	public static function assignStartEndMonth(DateTime $date, $prefix, $interval = '0 days')
	{
		$_date = self::moveDate($date, $interval);
		$_date->setTime(0,0,0);
		self::moveToDay($_date, 1);
		self::assignVar($_date, $prefix . 'date_from');
		
		self::moveToDay($_date, 31);
		self::assignVar($_date, $prefix . 'date_to');
	}
	/**
	 * Передает в Smarty значение, отформатированное согласно свойству $format, даты, полученной в результате смещения даты $date на $interval
	 *
	 * @param DateTime $date экземпляр класса DateTime
	 * @param string $prefix - имя переменной в Smarty
	 * @param string $interval - Строка даты/времени
	 */
	public static function assignOneDay(DateTime $date, $var_name, $interval = '0 days')
	{
		$_date = self::moveDate($date, $interval);
		$_date->setTime(0,0,0);
		self::assignVar($_date, $var_name);
	}
	/**
	 * Смещение даты $date в определенный день месяца
	 *
	 * @param DateTime $date экземпляр класса DateTime
	 * @param int $day - день месяца
	 */
	private static function moveToDay(DateTime $date, $day)
	{
		$year = $date->format('Y');
		$month = $date->format('m');
		$countDaysInMonth = $date->format('t');
		if ($day > $countDaysInMonth) 
		{
			$day = $countDaysInMonth;
		}
		if ($day <= 0) 
		{
			$day = $date->format('d');;
		}
		$date->setDate($year, $month, $day);
	}
	/**
	 * Смещение даты $date на $interval
	 *
	 * @param DateTime $date экземпляр класса DateTime
	 * @param string $interval - Строка даты/времени
	 */
	private static function moveDate(DateTime $date, $interval)
	{
		$_date = clone $date;
		$interval = DateInterval::createFromDateString($interval);
		$_date->add($interval);
		return $_date;
	}
	/**
	 * Передает в Smarty значение $day, отформатированную согласно свойству $format
	 * @param string $var_name имя переменной в Smarty
	 */
	private static function assignVar(DateTime $date, $var_name)
	{
		global $design;
		$design->assign($var_name, $date->format(self::$format));
	}
}