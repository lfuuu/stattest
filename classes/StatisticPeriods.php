<?php

class StatisticPeriods
{
	public static function assignPeriods(DateTime $date, $periods = array('cur_' => '0 month', 'today' => '0 days'))
	{
		global $design;
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
		global $design;
		$_date = clone $date;
		$interval = DateInterval::createFromDateString($interval);
		$_date->add($interval);
		$design->assign($prefix . 'date_from', $_date->format('01-m-Y'));
		$design->assign($prefix . 'date_to', $_date->format('t-m-Y'));
	}
	public static function assignOneDay(DateTime $date, $var_name, $interval = '0 days')
	{
		global $design;
		$_date = clone $date;
		$interval = DateInterval::createFromDateString($interval);
		$_date->add($interval);
		$design->assign($var_name, $_date->format('d-m-Y'));
	}
}