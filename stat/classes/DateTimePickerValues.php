<?php

class DateTimePickerValues extends DatePickerValues
{
    /** @var DateTime */
    public $dayTime;

    /**
     * @var string $format формат вывода переменной $dayTime, аналогично первому параметру функции date()
     */
    public $formatDateTime = 'd-m-Y H:i';

    /**
     * Инициализация переменной $dayTime
     *
     * @param string $formFieldName имя переменной в get запросе
     * @param string $defaultValue
     *
     * @throws Exception
     */
    protected function init($formFieldName, $defaultValue)
    {
        $dateTime = get_param_raw($formFieldName, '');
        $dayTime = null;
        if (!empty($dateTime)) {
            $dayTime = DateTime::createFromFormat($this->formatDateTime, $dateTime);
        }

        if (!is_object($dayTime)) {
            $dayTime = new DateTime();

            if (!empty($defaultValue)) {
                if ($defaultValue == 'first') {
                    $timestamp = strtotime('first day of ' . date('F Y'));
                } elseif ($defaultValue == 'next first') {
                    $timestamp = strtotime('first day of ' . date('F Y', strtotime('+1 month')));
                } else {
                    $timestamp = strtotime($defaultValue);
                }

                if ($timestamp !== false) {
                    $dayTime->setTimestamp($timestamp);
                }
            }
        }

        $this->dayTime = $dayTime;

        $day = clone $dayTime;
        $this->day = $day->setTime(0, 0, 0);
    }

    /**
     * Возвращает значение переменной
     *
     * @return DateTime
     */
    public function getValue()
    {
        return $this->dayTime;
    }

    /**
     * Возвращает отформатированное значение переменной
     *
     * @return string
     */
    public function getValueFormatted()
    {
        return $this->getValue()->format($this->formatDateTime);
    }

    /**
     * Возвращает временную метку Unix свойсва $day
     */
    public function getTimestamp()
    {
        return $this->dayTime->getTimestamp();
    }
}
