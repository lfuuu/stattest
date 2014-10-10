<?php
class DatePickerValues 
{
	/**
	 * @var DateTime $day экземпляр класса DateTime
	 */
	public $day;
	/**
	  * @var string $format формат вывода переменной $day, аналогично первому параметру функции date() 
	  */
	public $format = 'd-m-Y';
	/**
	  * @var string $sql_format формат вывода переменной $day 
	  */
	private $sql_format = 'Y-m-d';
	
	/**
	 * Инициализация объекта
	 *
	 * @param string $formFieldName имя переменной в get запросе
	 * @param string $default_day допустимые значения: "first" - первый день текущего месяца, "last" - последний день текущего месяца  или Строка даты/времени
	 * @param bool $assign передать значение $day в Smarty сразу после инициализации или нет
	 */
	function __construct($formFieldName, $default_day, $assign = true)
	{
		$this->initDay($formFieldName, $default_day);
		if ($assign) {
			$this->assignDay($formFieldName);
		}
	}
	/**
	 * Инициализация переменной $day
	 *
	 * @param string $formFieldName имя переменной в get запросе
	 * @param string $default_day допустимые значения: "first", "last"  или Строка даты/времени
	 * 
	 */
	private function initDay($formFieldName, $default_day)
	{
		$date = get_param_raw($formFieldName, '');
		$day = null;
		if (!empty($date))
		{
			$day = DateTime::createFromFormat($this->format, $date);
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
		$day->setTime(0,0,0);
		$this->day = $day;
	}
	/**
	 * Возвращает  $day, отформатированную согласно свойству $format
	 */
	public function getDay()
	{
		return $this->day->format($this->format);
	}
	/**
	 * Возвращает  $day, отформатированную согласно свойству $sql_format
	 */
	public function getSqlDay()
	{
		return $this->day->format($this->sql_format);
	}
	/**
	 * Возвращает временную метку Unix свойсва $day
	 */
	public function getTimestamp()
	{
		return $this->day->getTimestamp();
	}
	/**
	 * Передает в Smarty значение $day, отформатированное согласно свойству $format
	 * @param string $var_name имя переменной в Smarty
	 */
	public function assignDay($var_name)
	{
		global $design;
		$design->assign($var_name, $this->getDay());
	}
}
