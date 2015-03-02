<?php
namespace app\classes\bill;

use app\models\Transaction;
use Yii;
use DateTime;

class BillerPackageResource extends BillerPackage
{
    protected $freeAmount;
    protected $minPayment;
    protected $minPaymentTemplate;

    public function setFreeAmount($freeAmount)
    {
        $this->freeAmount = $freeAmount;
        return $this;
    }

    public function setMinPayment($minPayment)
    {
        $this->minPayment = $minPayment;
        return $this;
    }

    public function setMinPaymentTemplate($minPaymentTemplate)
    {
        $this->minPaymentTemplate = $minPaymentTemplate;
        return $this;
    }

    public function createTransaction()
    {
        $currentPeriod = $this->billerPeriodTo->getTimestamp() - $this->billerPeriodFrom->getTimestamp();
        $effectivePeriod = $this->billerActualTo->getTimestamp() - $this->billerActualFrom->getTimestamp();
        $minPayment = $this->minPayment * $effectivePeriod / $currentPeriod;


        $amount = $this->amount - ($this->freeAmount ? $this->freeAmount : 0);
        if ($amount < 0) {
            $amount = 0;
        }
        $amount = round($amount, 6);
        $price = $this->price;

        if (round($amount * $price, 2) < $minPayment) {
            $template = $this->minPaymentTemplate;
            $amount = 1;
            $price = $minPayment;
        } else {
            $template = $this->template;
        }

        if (!$price || !$amount) {
            return null;
        }

        $from = $this->billerActualFrom;
        $to = $this->billerActualTo;

        $transactionDate = clone $to;
        $transactionDate->modify('+1 second');


        $name = $this->processTemplate($from, $to, $template);


        $transaction = new Transaction();
        $transaction->client_account_id = $this->clientAccount->id;
        $transaction->source = Transaction::SOURCE_STAT;
        $transaction->billing_period = $this->billerPeriodFrom->format('Y-m-d');
        $transaction->service_type = $this->usage->getServiceType();
        $transaction->service_id = $this->usage->id;
        $transaction->transaction_type = Transaction::TYPE_RESOURCE;
        $transaction->name = $name;
        $transaction->transaction_date = $transactionDate->format('Y-m-d H:i:s');
        $transaction->period_from = $from->format('Y-m-d H:i:s');
        $transaction->period_to = $to->format('Y-m-d H:i:s');
        $transaction->amount = $amount;
        $transaction->price = $price;
        $transaction->is_partial_write_off = false;
        $this->calculateSum($transaction, $from, $to);

        return $transaction;
    }
}