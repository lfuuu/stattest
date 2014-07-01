<?php

class DatePickerPeriods
{
	private static $default_period = array('cur_' => '0 month', 'today' => '0 days');
	public static $format = 'd-m-Y';
	
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
		unset($date);
	}
	
	public static function assignStartEndMonth(DateTime $date, $prefix, $interval = '0 days')
	{
		$_date = self::moveDate($date, $interval);
		self::moveToDay($_date, 1);
		self::assignVar($_date, $prefix . 'date_from');
		
		self::moveToDay($_date, 31);
		self::assignVar($_date, $prefix . 'date_to');
	}
	
	public static function assignOneDay(DateTime $date, $var_name, $interval = '0 days')
	{
		$_date = self::moveDate($date, $interval);
		self::assignVar($_date, $var_name);
	}
	
	private function moveToDay(DateTime $date, $day)
	{
		$year = $date->format('Y');
		$month = $date->format('m');
		$countDaysInMonth = $date->format('t');
		if ($day > $countDaysInMonth) 
		{
			$day = $countDaysInMonth;
		}
		$date->setDate($year, $month, $day);
	}
	
	private function moveDate(DateTime $date, $interval)
	{
		$_date = clone $date;
		$interval = DateInterval::createFromDateString($interval);
		$_date->add($interval);
		return $_date;
	}
	
	private function assignVar(DateTime $date, $var_name)
	{
		global $design;
		$design->assign($var_name, $date->format(self::$format));
	}
}