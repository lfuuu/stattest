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
	  * @var string $sqlFormat формат вывода переменной $day
	  */
	protected $sqlFormat = 'Y-m-d';

    /**
     * Инициализация объекта
     *
     * @param string $formFieldName имя переменной в get запросе
     * @param string $defaultValue допустимые значения: "first" - первый день текущего месяца, "last" - последний день текущего месяца  или Строка даты/времени
     * @param bool $assign передать значение $day в Smarty сразу после инициализации или нет
     * @throws Exception
     */
	public function __construct($formFieldName, $defaultValue, $assign = true)
	{
		$this->init($formFieldName, $defaultValue);
		if ($assign) {
			$this->assignValue($formFieldName);
		}
	}

    /**
     * Инициализация переменной $day
     *
     * @param string $formFieldName имя переменной в get запросе
     * @param string $defaultValue допустимые значения: "first", "last"  или Строка даты/времени
     *
     * @throws Exception
     */
	protected function init($formFieldName, $defaultValue)
	{
		$date = get_param_raw($formFieldName, '');
		$day = null;
		if (!empty($date)) {
			$day = DateTime::createFromFormat($this->format, $date);
		}

		if (!is_object($day)) {
			$day = new DateTime();

			if (!empty($defaultValue)) {
				if ($defaultValue == 'first') {
					$timestamp = strtotime('first day of ' . date('F Y'));
				} elseif ($defaultValue == 'last') {
					$timestamp = strtotime('last day of ' . date('F Y'));
				} else {
					$timestamp = strtotime($defaultValue);
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
     * Возвращает значение переменной
     *
     * @return DateTime
     */
    public function getValue()
    {
        return $this->day;
    }

    /**
     * Возвращает отформатированное значение переменной
     *
     * @return string
     */
    public function getValueFormatted()
    {
        return $this->getValue()->format($this->format);
    }

	/**
	 * Возвращает $day, отформатированную согласно свойству $format
	 */
	public function getDay()
	{
		return $this->day->format($this->format);
	}

	/**
	 * Возвращает $day, отформатированную согласно свойству $this->sqlFormat
	 */
	public function getSqlDay()
	{
		return $this->day->format($this->sqlFormat);
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
	 * @param string $varName имя переменной в Smarty
	 */
	public function assignValue($varName)
	{
		global $design;
        $design->assign($varName, $this->getValueFormatted());
	}
}
