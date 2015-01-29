<?php
namespace app\dao;

use app\classes\Assert;
use app\classes\Singleton;
use app\models\Bill;
use app\models\BillLine;
use app\models\Payment;
use app\models\Transaction;

/**
 * @method static TransactionDao me($args = null)
 * @property
 */
class TransactionDao extends Singleton
{
    public function copy(Transaction $from, Transaction $to)
    {
        $to->name                   = $from->name;
        $to->transaction_date       = $from->transaction_date;
        $to->amount                 = $from->amount;
        $to->price                  = $from->price;
        $to->is_partial_write_off   = $from->is_partial_write_off;
        $to->tax_type_id            = $from->tax_type_id;
        $to->sum                    = $from->sum;
        $to->sum_tax                = $from->sum_tax;
        $to->sum_without_tax        = $from->sum_without_tax;
        $to->effective_amount       = $from->effective_amount;
        $to->effective_sum          = $from->effective_sum;
    }

    public function insertByPayment(Payment $payment)
    {
        $transaction = new Transaction();
        $transaction->client_account_id = $payment->client_id;
        $transaction->source = Transaction::SOURCE_PAYMENT;
        $transaction->transaction_date = $payment->payment_date;
        $transaction->sum = $payment->sum;
        $transaction->effective_sum = $payment->sum;
        $transaction->is_partial_write_off = false;
        $transaction->payment_id = $payment->id;
        $transaction->save();
    }

    public function updateByPayment(Payment $payment)
    {
        $transaction = $this->getByPaymentId($payment->id);

        if ($transaction === null) {
            $transaction = new Transaction();
        }

        $transaction->client_account_id = $payment->client_id;
        $transaction->source = Transaction::SOURCE_PAYMENT;
        $transaction->transaction_date = $payment->payment_date;
        $transaction->sum = $payment->sum;
        $transaction->effective_sum = $payment->sum;
        $transaction->is_partial_write_off = false;
        $transaction->payment_id = $payment->id;
        $transaction->save();
    }

    public function deleteByPaymentId($paymentId)
    {
        $transaction = $this->getByPaymentId($paymentId);

        if ($transaction !== null) {
            $transaction->delete();
        }
    }

    public function markDeleted(Transaction $transaction, $deleted = true)
    {
        $transaction->deleted = $deleted;
        if ($deleted) {
            $transaction->effective_amount = null;
            $transaction->effective_sum = 0;
            $transaction->bill_id = null;
            $transaction->bill_line_id = null;
        }
        $transaction->save();
    }


    public function insertByBillLine(Bill $bill, BillLine $line)
    {
        $transaction = new Transaction();
        $transaction->client_account_id = $bill->client_id;
        $transaction->source = Transaction::SOURCE_BILL;

        $this->fillTransactionByBillLine($transaction, $line, $bill);

        $transaction->save();
    }

    public function updateByBillLine(Bill $bill, BillLine $line, Transaction $transaction = null)
    {
        if ($transaction === null) {
            $transaction = Transaction::findOne(['bill_line_id' => $line->pk]);
            Assert::isObject($transaction);
        }

        $this->fillTransactionByBillLine($transaction, $line, $bill);

        $transaction->save();
    }

    public function deleteByBillLine(BillLine $line)
    {
        Transaction::deleteAll(['bill_line_id' => $line->pk]);
    }

    public function insertBillLine(Transaction $transaction, Bill $bill, $billLinePosition)
    {
        $line = new BillLine();
        $line->sort = $billLinePosition;
        $line->bill_no = $bill->bill_no;
        $this->fillBillLineByTransaction($line, $transaction);
        $line->save();

        $transaction->bill_id = $bill->id;
        $transaction->bill_line_id = $line->pk;
        $transaction->save();
    }

    public function updateBillLine(Transaction $transaction)
    {
        $line = BillLine::findOne($transaction->bill_line_id);
        $this->fillBillLineByTransaction($line, $transaction);
        $line->save();
    }

    public function deleteBillLine(Transaction $transaction)
    {
        if ($transaction->bill_line_id) {
            BillLine::deleteAll(['pk' => $transaction->bill_line_id]);
        }
    }

    private function fillTransactionByBillLine(Transaction $transaction, BillLine $line, Bill $bill)
    {
        $transaction->name = $line->item;
        $transaction->billing_period = $line->date_from;
        $transaction->service_type = $line->service;
        $transaction->service_id = $line->id_service;
        $transaction->transaction_date = $bill->bill_date;
        $transaction->price = $line->price;
        $transaction->amount = $line->amount;
        $transaction->tax_type_id = $line->tax_type_id;
        $transaction->sum = $line->sum;
        $transaction->sum_tax = $line->sum_tax;
        $transaction->sum_without_tax = $line->sum_without_tax;
        $transaction->is_partial_write_off = 0;
        $transaction->effective_amount = $line->amount;
        $transaction->effective_sum = $line->sum;
        $transaction->bill_id = $bill->id;
        $transaction->bill_line_id = $line->pk;
    }

    private function fillBillLineByTransaction(BillLine $line, Transaction $transaction)
    {
        $year = substr($transaction->billing_period, 0, 4);
        $month = substr($transaction->billing_period, 5, 2);

        $line->item = $transaction->name;
        $line->date_from = $year . '-' . $month . '-01';
        $line->date_to = $year . '-' . $month . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $line->type = 'service';
        $line->amount = $transaction->amount;
        $line->price = $transaction->price;
        $line->tax_type_id = $transaction->tax_type_id;
        $line->sum = $transaction->sum;
        $line->sum_without_tax = $transaction->sum_without_tax;
        $line->sum_tax = $transaction->sum_tax;
        $line->service = $transaction->service_type;
        $line->id_service = $transaction->service_id;
    }

    /**
     * @return Transaction
     */
    public function getByPaymentId($paymentId)
    {
        return
            Transaction::find()
                ->andWhere(['payment_id' => $paymentId])
                ->limit(1)
                ->one();
    }


}