<?php

namespace app\modules\uu\tarificator;

/**
 * Class Tarificator
 */
abstract class Tarificator
{
    private $_isEcho = false;

    /**
     * @param bool $isEcho
     */
    public function __construct($isEcho = false)
    {
        $this->_isEcho = $isEcho;
    }

    /**
     * Вывод на консоль строку
     *
     * @param string $string
     */
    protected function out($string)
    {
        if ($this->_isEcho) {
            echo $string;
        }
    }

    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    abstract public function tarificate($accountTariffId = null);
}
