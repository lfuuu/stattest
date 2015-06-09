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
            print 'here';
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
        $date = $this->biller->billerPeriodFrom;
        $this->currentFrom = clone $this->usageActualFrom;
        $this->currentTo = clone $this->usageActualFrom;

        $yearFrom  = (int)$date->format('Y');
        $yearTo    = $yearFrom;
        $monthFrom = (int)$date->format('m');
        $monthTo   = $monthFrom;

        $usageMonth = (int)$this->usageActualFrom->format('m');
        $usageDay   = (int)$this->usageActualFrom->format('d');

        if ($this->isAlign) {
            $dayFrom = $dayTo = 1;
            $offset = 0;
        } else {
            $dayFrom = $dayTo = $usageDay;
            $offset = $usageMonth - 1;
        }

        if ($this->periodType == Biller::PERIOD_YEAR) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                $this->calcPeriod(12, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($this->periodType == Biller::PERIOD_6_MONTH) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                $this->calcPeriod(6, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($this->periodType == Biller::PERIOD_3_MONTH) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                $this->calcPeriod(3, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($this->periodType == Biller::PERIOD_MONTH) {

            list($yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo) =
                $this->calcPeriod(1, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo);

        } elseif ($this->periodType == Biller::PERIOD_ONCE) {
            $yearFrom = $yearTo = $this->usageActualFrom->format('Y');
            $monthFrom = $monthTo = $this->usageActualFrom->format('m');
            $dayFrom = $dayTo = $this->usageActualFrom->format('d');
            $this->currentTo->setTime(0, 0, 1);
        } else {
            return false;
        }

        $this->currentFrom->setDate($yearFrom, $monthFrom, $dayFrom);
        $this->currentTo->setDate($yearTo, $monthTo, $dayTo);
        $this->currentTo->modify('-1 second');


        if ($this->isAlign) {

            if ($this->currentFrom > $this->billerActualTo || $this->currentTo < $this->billerActualFrom) {
                return false;
            }

            $this->currentAlignFrom = clone $this->currentFrom;
            $this->currentAlignTo = clone $this->currentTo;

            if ($this->currentAlignFrom < $this->billerActualFrom) {
                $this->currentAlignFrom = clone $this->billerActualFrom;
            }

            if ($this->currentAlignTo > $this->billerActualTo) {
                $this->currentAlignTo = clone $this->billerActualTo;
            }

            if ($this->currentAlignFrom > $this->billerPeriodTo || $this->currentAlignTo < $this->billerPeriodFrom) {
                return false;
            }

        } else {

            if ($this->currentFrom < $this->billerActualFrom || $this->currentFrom > $this->billerActualTo) {
                return false;
            }

            $this->currentAlignFrom = clone $this->currentFrom;
            $this->currentAlignTo = clone $this->currentTo;
        }

        return true;
    }

    private function calcPeriod($period, $offset, $yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo)
    {
        $monthFrom = (ceil(($monthFrom - $offset) / $period) - 1) * $period + 1 + $offset;
        $monthTo = (ceil(($monthTo - $offset) / $period)) * $period + 1 + $offset;

        if ($monthFrom > 12) {
            $monthFrom -= 12;
            $yearFrom++;
        } elseif ($monthFrom < 1) {
            $monthFrom += 12;
            $yearFrom--;
        }

        if ($monthTo > 12) {
            $monthTo -= 12;
            $yearTo++;
        } elseif ($monthTo < 1) {
            $monthTo += 12;
            $yearTo--;
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthFrom, $yearFrom);
        $dayFrom = $dayFrom > $daysInMonth ? $daysInMonth : $dayFrom;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthTo, $yearTo);
        $dayTo = $dayTo > $daysInMonth ? $daysInMonth : $dayTo;

        return [$yearFrom, $monthFrom, $dayFrom, $yearTo, $monthTo, $dayTo];
    }
}