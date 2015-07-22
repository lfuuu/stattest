<?php
namespace app\classes\bill;

use app\models\Transaction;
use Yii;
use DateTime;

class BillerPackagePeriodical extends BillerPackage
{
    protected $isAlign;
    protected $isPartialWriteOff;

    public function setIsAlign($isAlign)
    {
        $this->isAlign = $isAlign;
        return $this;
    }

    public function setIsPartialWriteOff($isPartialWriteOff)
    {
        $this->isPartialWriteOff = $isPartialWriteOff;
        return $this;
    }

    public function createTransaction()
    {
        if (!$this->prepareCurrentPeriod()) {
            return null;
        }

        if (!$this->price || !$this->amount) {
            return null;
        }

        $from = $this->currentAlignFrom;
        $to = $this->currentAlignTo;
        $amount = $this->amount;

        if ($this->isAlign) {
            $currentPeriod = $this->currentTo->getTimestamp() - $this->currentFrom->getTimestamp();
            $effectivePeriod = $to->getTimestamp() - $from->getTimestamp();

            if ($currentPeriod > 0) {
                $amount = $amount * $effectivePeriod / $currentPeriod;
            }
        }

        $name = $this->processTemplate($from, $to);


        $transaction = new Transaction();
        $transaction->client_account_id = $this->clientAccount->id;
        $transaction->source = Transaction::SOURCE_STAT;
        $transaction->billing_period = $this->billerPeriodFrom->format('Y-m-d');
        $transaction->service_type = $this->usage->getServiceType();
        $transaction->service_id = $this->usage->id;
        $transaction->transaction_type = Transaction::TYPE_PERIODICAL;
        $transaction->name = $name;
        $transaction->transaction_date = $from->format('Y-m-d H:i:s');
        $transaction->period_from = $from->format('Y-m-d H:i:s');
        $transaction->period_to = $to->format('Y-m-d H:i:s');
        $transaction->amount = $amount;
        $transaction->price = $this->price;
        $transaction->is_partial_write_off = $this->isPartialWriteOff;
        $this->calculateSum($transaction, $from, $to);

        return $transaction;
    }

    protected function prepareCurrentPeriod()
    {
        $usagePeriod =
            BillUtils::prepareUsagePeriod(
                $this->biller->billerPeriodFrom,
                $this->usageActualFrom,
                $this->periodType,
                $this->isAlign
            );
        if (!$usagePeriod) {
            return false;
        }

        list($this->currentFrom, $this->currentTo) = $usagePeriod;

        if ($this->currentFrom === null ||  $this->currentTo === null) {
            return false;
        }

        if ($this->isAlign) {

            $this->currentAlignFrom = clone $this->currentFrom;
            $this->currentAlignTo = clone $this->currentTo;

            if ($this->currentAlignFrom < $this->billerActualFrom) {
                $this->currentAlignFrom = clone $this->billerActualFrom;
            }

            if ($this->currentAlignTo > $this->billerActualTo) {
                $this->currentAlignTo = clone $this->billerActualTo;
            }

        } else {
            $this->currentAlignFrom = clone $this->currentFrom;
            $this->currentAlignTo = clone $this->currentTo;
        }

        if ($this->isAlign) {

            if ($this->currentFrom > $this->billerActualTo || $this->currentTo < $this->billerActualFrom) {
                return false;
            }

            if ($this->currentAlignFrom > $this->billerPeriodTo || $this->currentAlignTo < $this->billerPeriodFrom) {
                return false;
            }

        } else {

            if ($this->currentFrom < $this->billerActualFrom || $this->currentFrom > $this->billerActualTo) {
                return false;
            }

        }

        return true;
    }
}