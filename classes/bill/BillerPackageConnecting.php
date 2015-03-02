<?php
namespace app\classes\bill;

use app\models\Transaction;
use Yii;

class BillerPackageConnecting extends BillerPackage
{

    public function createTransaction()
    {
        if (
            $this->usageActualFrom >= $this->billerPeriodFrom
            && $this->usageActualFrom <= $this->billerPeriodTo
            && $this->price
            && $this->amount
        ) {
            $name = $this->processTemplate($this->usageActualFrom, $this->usageActualTo);

            $transaction = new Transaction();
            $transaction->client_account_id = $this->clientAccount->id;
            $transaction->source = Transaction::SOURCE_STAT;
            $transaction->billing_period = $this->billerPeriodFrom->format('Y-m-d');
            $transaction->service_type = $this->usage->getServiceType();
            $transaction->service_id = $this->usage->id;
            $transaction->transaction_type = Transaction::TYPE_CONNECTING;
            $transaction->name = $name;
            $transaction->transaction_date = $this->usageActualFrom->format('Y-m-d H:i:s');
            $transaction->amount = $this->amount;
            $transaction->price = $this->price;
            $transaction->is_partial_write_off = false;
            $this->calculateSum($transaction);
            return $transaction;
        }
    }

}