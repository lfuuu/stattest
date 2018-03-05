<?php

namespace app\modules\uu\resourceReader;

class Amounts
{
    public $amount; // кол-во ресурса, стоимость. Если null -значение неизвестно
    public $costAmount; // себестоимость

    /**
     * @param int|float|null $amount
     * @param int|float|null $costAmount
     */
    public function __construct($amount = null, $costAmount = null)
    {
        $amount !== null && $this->amount = $amount;
        $costAmount !== null && $this->costAmount = $costAmount;
    }
}