<?php
class DatePickerValues 
{
	public $day;
	public $format = 'd-m-Y';
	
	function __construct($_day, $default_day, $assign = true)
	{
		$this->initDay($_day, $default_day);
		if ($assign) {
			$this->assignDay($_day);
		}
	}
	private function initDay($_day, $default_day)
	{
		$_date = get_param_raw($_day, '');
		$day = null;
		if (!empty($_date))
		{
			$day = DateTime::createFromFormat($this->format, $_date);
		}
		if (!is_object($day))
		{
			$day = new DateTime();
			if (!empty($default_day)) 
			{
				if ($default_day == 'first')
				{
					$timestamp = strtotime('first day of ' . date('F Y'));
				} elseif ($default_day == 'last') {
					$timestamp = strtotime('last day of ' . date('F Y'));
				} else {
					$timestamp = strtotime($default_day);
				}
				if ($timestamp !== false) {
					$day->setTimestamp($timestamp);
				}
			}
		}
		$this->day = $day;
	}
	public function getDay()
	{
		return $this->day->format($this->format);
	}
	public function getTimestamp()
	{
		return $this->day->getTimestamp();
	}
	public function assignDay($var_name)
	{
		global $design;
		$design->assign($var_name, $this->getDay());
	}
}
