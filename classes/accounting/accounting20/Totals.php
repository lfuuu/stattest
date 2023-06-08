<?php

namespace app\classes\accounting\accounting20;

use app\models\BillExternal;
use yii\base\BaseObject;

/**
 * Class totals
 *
 * @property float totalPlus
 * @property float totalMinus
 * @property float totalBills
 */
class Totals extends BaseObject
{
    public float $invSum = 0;

    public float $billSumPlus = 0;
    public float $billSumMinus = 0;

    public float $paysPlusSum = 0;
    public float $paysMinusSum = 0;

    public float $invoiceExtSum = 0;
    public float $invoiceExtPays = 0;

    public float $paysPlusBills = 0;
    public float $paysMinusBills = 0;
    public float $paysPlusInv = 0;

    public ?Lists $lists = null;

    public function getTotalPlus(): float
    {
        return $this->paysPlusSum - $this->billSumPlus;
    }

    public function getTotalMinus(): float
    {
        return $this->billSumMinus - $this->paysMinusSum;
    }

    public function getTotalBills(): float
    {
        return $this->totalPlus - $this->totalMinus;
    }

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->lists) {
            throw new \InvalidArgumentException('Lists not set');
        }

        $lists = $this->lists;

        $this->invSum = array_reduce($lists->invoices, function ($acum, $i) {
            return $acum + $i->sum;
        }, 0);

        $this->billSumPlus = array_reduce($lists->billsPlus, function ($acum, $i) {
            return $acum + $i->sum;
        }, 0);

        $this->billSumMinus = array_reduce($lists->billsMinus, function ($acum, $i) {
            return $acum + $i->sum;
        }, 0);

        $this->invoiceExtSum = array_reduce($lists->invoiceExt, function ($acum, BillExternal $i) {
            return $acum + $i->ext_vat + $i->ext_sum_without_vat;
        }, 0);

        $this->paysPlusSum = array_reduce($lists->paysPlus, function ($acum, $i) {
            return $acum + $i->sum;
        }, 0);

        $this->paysMinusSum = array_reduce($lists->paysMinus, function ($acum, $i) {
            return $acum + $i->sum;
        }, 0);

        $this->paysPlusInv = $this->paysPlusBills = $this->paysPlusSum;
        $this->invoiceExtPays = $this->paysMinusBills = $this->paysMinusSum;
    }
}