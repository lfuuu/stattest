<?php
namespace app\dao;

use app\classes\bill\SubscriptionCalculator;
use app\classes\Singleton;
use app\classes\Utils;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\CurrencyRate;
use app\models\Payment;

/**
 * @method static BillDao me($args = null)
 * @property
 */
class BillDao extends Singleton
{

    public function updateSubscriptionForAllClientAccounts()
    {
        session_write_close();
        set_time_limit(0);

        $accounts =
            ClientAccount::find()
                ->select('id')
                ->andWhere("status not in ( 'closed', 'trash', 'once', 'tech_deny', 'double', 'deny')")
                ->andWhere("credit > -1")
                ->all()
        ;

        foreach ($accounts as $account) {
            $this->updateSubscription($account->id);
        }
    }

    public function updateSubscription($clientAccountId)
    {
        $lastAccountDate = ClientAccount::dao()->getLastBillDate($clientAccountId);

        SubscriptionCalculator::create()
            ->setClientAccountId($clientAccountId)
            ->calculate($lastAccountDate)
            ->save();
    }

    public function requestRateForBill(Bill $bill)
    {
        return
            CurrencyRate::dao()
                ->findRate(
                    'USD',
                    $bill->bill_date
                )->rate;
    }

    public function requestRateForInvoice1(Bill $bill)
    {
        return
            CurrencyRate::dao()
                ->findRate(
                    'USD',
                    Utils::dateEndOfMonth($bill->bill_date)
                )->rate;
    }

    public function requestRateForInvoice2(Bill $bill)
    {
        return
            CurrencyRate::dao()
                ->findRate(
                    'USD',
                    Utils::dateEndOfPreviousMonth($bill->bill_date)
                )->rate;
    }

    public function requestRateForInvoice3(Bill $bill)
    {
        return
            CurrencyRate::dao()
                ->findRate(
                    'USD',
                    $bill->bill_date
                )->rate;
    }

    public function requestRateForInvoice4(Bill $bill)
    {
        $payment = Payment::findOne(['bill_no' => $bill->bill_no]);

        return $payment ? $payment->payment_rate : '';
    }

    public function requestSumRubForBill(Bill $bill)
    {
        $sumRub =
            Payment::getDb()->createCommand("
                    select
                        sum(sum_rub)
                    from
                        newpayments as P
                    where
                        bill_no=:billNo
                ",
                [':billNo' => $bill->bill_no]
            )->queryScalar();

        return $sumRub !== null ? round($sumRub,2) : null;
    }

    public function recalcBill(Bill $bill)
    {
        $this->recalcBillLines($bill);
        $this->recalcBillSum($bill);
    }

    public function recalcBillSum(Bill $bill)
    {
        $bill->sum_total_with_unapproved =
            BillLine::find()
                ->andWhere(['bill_no' => $bill->bill_no])
                ->andWhere("type <> 'zadatok'")
                ->sum('sum_with_tax');

        if ($bill->sum_total_with_unapproved === null) {
            $bill->sum_total_with_unapproved = 0;
        }

        $bill->sum_total =
            $bill->cleared_flag
                ? $bill->sum_total_with_unapproved
                : 0;

        $bill->save();
    }

    public function recalcBillLines(Bill $bill)
    {
        /** @var BillLine[] $lines */
        $lines = $bill->lines;
        foreach ($lines as $line) {
            $this->recalcBillLineSum($bill, $line);
        }
    }

    public function recalcBillLineSum(Bill $bill, BillLine $line)
    {
        $line->calcSum($bill);
        $line->save();
    }
}