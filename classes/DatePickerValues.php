<?php
class DatePickerValues 
{
	public $day;
	
	function __construct($_day, $default_day, $format = 'd-m-Y', $assign = true)
	{
		$this->initDay($_day, $format, $default_day);
		if ($assign) {
			$this->assignDay($_day);
		}
	}
	private function initDay($_day, $format, $default_day)
	{
		$_date = get_param_raw($_day, '');
		$day = '';
		if (!empty($_date))
		{
			$day = DateTime::createFromFormat($format, $_date);
		}
		if (!is_object($day))
		{
			$day = new DateTime();
			if (!empty($default_day) && ($timestamp = strtotime($default_day)) !== false) 
			{
				$day->setTimestamp($timestamp);
			}
		}
		$this->day = $day;
	}
	public function getDay($format = 'd-m-Y')
	{
		return $this->day->format($format);
	}
	public function getTimestamp()
	{
		return $this->day->getTimestamp();
	}
	public function assignDay($var_name, $format = 'd-m-Y')
	{
		global $design;
		$design->assign($var_name, $this->getDay($format));
	}
}
