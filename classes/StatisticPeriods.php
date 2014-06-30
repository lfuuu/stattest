<?php

class StatisticPeriods
{
	public $day;
	
	private $default_dates = array(
					'year' => 0,
					'month' => 0,
					'day' => 0
				);
	
	
	function __construct(DateTime $day)
	{
		$this->day = $day;
		$this->initDefaultDates();
	}
	private function initDefaultDates() {
		$this->default_dates['year'] = $this->day->format('Y');
		$this->default_dates['month'] = $this->day->format('m');
		$this->default_dates['day'] = $this->day->format('d');
	}
	private function getDay($format = 'd-m-Y')
	{
		return $this->day->format($format);
	}
	private function moveDate($interval)
	{
		$this->day->add($interval);
	}
	private function resetDates()
	{
		$this->day->setDate($this->default_dates['year'], $this->default_dates['month'], $this->default_dates['day']);
	}
	private function setToDay($day, $month = '', $year = '')
	{
		if (empty($month))
		{
			$month = $this->getDay('m');
		}
		if (empty($year))
		{
			$year = $this->getDay('Y');
		}
		$this->day->setDate($year, $month, $day);
	}
	
	function getTimestamp($date) 
	{
		return $this->day->getTimestamp();
	}
	function assignPeriods($periods)
	{
		global $design;
		foreach ($periods as $prefix => $v) {
			$interval = DateInterval::createFromDateString($v);
			if (strpos($v, 'month') === false) {
				$this->moveDate($interval);
				$design->assign($prefix, $this->getDay());
				$this->resetDates();
			} else {
				$this->setToDay(1);
				$this->moveDate($interval);
				$design->assign($prefix . 'date_from', $this->getDay());
				$design->assign($prefix . 'date_to', $this->getDay('t-m-Y'));
				$this->resetDates();
			} 
		}
	}
	
}