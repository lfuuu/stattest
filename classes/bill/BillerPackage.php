<?php
namespace app\classes\bill;

use app\models\ClientAccount;
use app\models\TaxType;
use app\models\Transaction;
use app\models\Usage;
use Yii;
use DateTime;

abstract class BillerPackage
{
    /** @var Biller */
    protected $biller;
    /** @var ClientAccount */
    protected $clientAccount;
    /** @var Usage */
    protected $usage;

    /** @var DateTime */
    protected $currentFrom;
    /** @var DateTime */
    protected $currentTo;
    /** @var DateTime */
    protected $currentAlignFrom;
    /** @var DateTime */
    protected $currentAlignTo;

    protected $name;
    protected $template = '{name}';
    protected $amount = 1;
    protected $price;

    /** @var DateTime */
    protected $billerPeriodFrom;
    /** @var DateTime */
    protected $billerPeriodTo;
    /** @var DateTime */
    protected $billerActualFrom;
    /** @var DateTime */
    protected $billerActualTo;
    /** @var DateTime */
    protected $usageActualFrom;
    /** @var DateTime */
    protected $usageActualTo;

    public static function create(Biller $biller)
    {
        $package = new static();
        $package->biller            = $biller;
        $package->clientAccount     = $biller->clientAccount;
        $package->usage             = $biller->usage;
        $package->billerPeriodFrom  = $biller->billerPeriodFrom;
        $package->billerPeriodTo    = $biller->billerPeriodTo;
        $package->billerActualFrom  = $biller->billerActualFrom;
        $package->billerActualTo    = $biller->billerActualTo;
        $package->usageActualFrom   = $biller->usageActualFrom;
        $package->usageActualTo     = $biller->usageActualTo;
        return $package;
    }

    public function setActualPeriod(DateTime $from, DateTime $to)
    {
        $this->billerActualFrom = $from;
        $this->billerActualTo = $to;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = round($amount, 6);
        return $this;
    }

    public function setPrice($price)
    {
        $this->price = round($price, 4);
        return $this;
    }

    abstract public function createTransaction();

    protected function calculateSum(Transaction $transaction, DateTime $periodFrom = null, DateTime $periodTo = null)
    {
        $transaction->tax_type_id = $this->clientAccount->getDefaultTaxId();

        $transaction->sum_without_tax = round($transaction->amount * $transaction->price, 2);
        $transaction->sum_tax = round($transaction->sum_without_tax * TaxType::rate($transaction->tax_type_id), 2);
        $transaction->sum = $transaction->sum_without_tax + $transaction->sum_tax;

        if ($transaction->is_partial_write_off && $periodFrom && $periodTo) {
            $date = $this->biller->billerDate->getTimestamp();
            $periodFrom = $periodFrom->getTimestamp();
            $periodTo = $periodTo->getTimestamp();

            if ($date < $periodFrom) {
                $transaction->effective_amount = 0;
                $transaction->effective_sum = 0;
            } elseif ($date > $periodTo) {
                $transaction->effective_sum = - $transaction->sum;
            } elseif ($periodTo > $periodFrom) {
                $all = $periodTo - $periodFrom + 1;
                $done = $date - $periodFrom;

                $transaction->effective_amount = round($transaction->amount * $done / $all, 6);
                $transaction->effective_sum = - round($transaction->effective_amount * $transaction->price, 2);
            } else {
                $transaction->effective_amount = $transaction->amount;
                $transaction->effective_sum = - $transaction->sum;
            }
        } else {
            $transaction->effective_amount = $transaction->amount;
            $transaction->effective_sum = - $transaction->sum;
        }
    }

}