<?php
class StatisticDays 
{
	public $day;
	
	function __construct($_day, $isFirstDay, $format = 'd-m-Y', $assign = true)
	{
		$this->initDay($_day, $format, $isFirstDay);
		if ($assign) {
			$this->assignDay($_day);
		}
	}
	private function initDay($_day, $format, $isFirstDay)
	{
		$_date = get_param_raw($_day, '');
		$day = '';
		if (!empty($_date))
		{
			$day = DateTime::createFromFormat($format, $_date);
		}
		if (!is_object($day))
		{
			if ($isFirstDay == 'FirstDayOfMonth') 
			{
				$day = DateTime::createFromFormat('d-m-Y', date('01-m-Y'));
			} elseif ($isFirstDay == 'LastDayOfMonth') {
				$day = DateTime::createFromFormat('d-m-Y', date('t-m-Y'));
			} else {
				$day = new DateTime();
			}
		}
		$this->day = $day;
	}
	function getDay($format = 'd-m-Y')
	{
		return $this->day->format($format);
	}
	function getTimestamp()
	{
		return $this->day->getTimestamp();
	}
	function assignDay($var_name, $format = 'd-m-Y')
	{
		global $design;
		$design->assign($var_name, $this->getDay($format));
	}
}
