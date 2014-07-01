<?php

class DatePickerPeriods
{
	public static $default_period = array('cur_' => '0 month', 'today' => '0 days');
	
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
		self::assignVar($_date, $prefix . 'date_from', '01-m-Y');
		self::assignVar($_date, $prefix . 'date_to', 't-m-Y');
	}
	
	public static function assignOneDay(DateTime $date, $var_name, $interval = '0 days')
	{
		$_date = self::moveDate($date, $interval);
		self::assignVar($_date, $var_name);
	}
	
	private function moveDate(DateTime $date, $interval)
	{
		$_date = clone $date;
		$interval = DateInterval::createFromDateString($interval);
		$_date->add($interval);
		return $_date;
	}
	
	private function assignVar(DateTime $date, $var_name, $format = 'd-m-Y')
	{
		global $design;
		$design->assign($var_name, $date->format($format));
	}
}