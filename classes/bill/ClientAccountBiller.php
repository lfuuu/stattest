<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Emails;
use app\models\Transaction;
use app\models\Usage;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use Yii;
use DateTime;
use DateTimeZone;

class ClientAccountBiller
{
    /** @var DateTimeZone */
    public $timezone;
    /** @var DateTime */
    protected $billerDate;
    /** @var DateTime */
    public $billerPeriodFrom;
    /** @var DateTime */
    public $billerPeriodTo;

    /** @var ClientAccount */
    protected $clientAccount;
    /** @var Transaction[] */
    protected $transactions = [];
    /** @var BillLine[] */
    protected $billLines = [];
    protected $errors = [];

    /**
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @return ClientAccountBiller
     */
    public static function create(ClientAccount $clientAccount, DateTime $date)
    {
        return new static($clientAccount, $date);
    }

    protected function __construct(ClientAccount $clientAccount, DateTime $date)
    {
        $this->billerDate = $date;
        $this->clientAccount = $clientAccount;
        $this->setupBillerPeriod();
    }

    protected function setupTimezone()
    {
        $this->timezone = new DateTimeZone($this->clientAccount->accountRegion->timezone_name);

        Assert::isObject($this->timezone);
    }

    protected function setupBillerDate(DateTime $date)
    {
        $this->billerDate = clone $date;
        $this->billerDate->setTimezone($this->timezone);
    }

    protected function setupBillerPeriod()
    {
        $year = $this->billerDate->format('Y');
        $month = $this->billerDate->format('m');

        $this->billerPeriodFrom = clone $this->billerDate;
        $this->billerPeriodFrom->setDate($year, $month, 1);
        $this->billerPeriodFrom->setTime(0, 0, 0);

        $this->billerPeriodTo = clone $this->billerDate;
        $this->billerPeriodTo->setDate($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
        $this->billerPeriodTo->setTime(23, 59, 59);
    }

    public function createTransactions()
    {
        $this->transactions = [];

        $this->processUsages(
            UsageIpPorts::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );

        $this->processUsages(
            UsageVoip::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );


        $this->processUsages(
            UsageVirtpbx::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );

        $this->processUsages(
            UsageExtra::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );

        $this->processUsages(
            UsageWelltime::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );

        $this->processUsages(
            Emails::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );

        $this->processUsages(
            UsageSms::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d')])
                ->all()
        );

        return $this;
    }

    public function saveTransactions()
    {
        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var Transaction[] $transactions */
            $transactions =
                Transaction::find()
                    ->andWhere([
                        'client_account_id' => $this->clientAccount->id,
                        'source' => Transaction::SOURCE_STAT,
                        'billing_period' => $this->billerPeriodFrom->format('Y-m-d'),
                    ])
                    ->orderBy('id')
                    ->all();

            $existsTransactionsByKey = [];
            foreach ($transactions as $transaction) {
                $key = $transaction->service_type . '_' . $transaction->service_id . '_' . $transaction->package_id . '_' . $transaction->transaction_type;
                if (!isset($existsTransactionsByKey[$key])) {
                    $existsTransactionsByKey[$key] = [];
                }
                $existsTransactionsByKey[$key][] = $transaction;
            }

            $tochedBillIds = [];

            foreach ($this->transactions as $k => $transaction) {
                $key = $transaction->service_type . '_' . $transaction->service_id . '_' . $transaction->package_id . '_' . $transaction->transaction_type;
                if (isset($existsTransactionsByKey[$key])) {
                    $existsTransaction = array_shift($existsTransactionsByKey[$key]);
                    if (empty($existsTransactionsByKey[$key])) {
                        unset($existsTransactionsByKey[$key]);
                    }

                    Transaction::dao()->copy($transaction, $existsTransaction);

                    if ($existsTransaction->bill_id && $existsTransaction->bill_line_id) {
                        if (!isset($tochedBillIds[$existsTransaction->bill_id])) {
                            $tochedBillIds[$existsTransaction->bill_id] = $existsTransaction->bill_id;
                        }

                        Transaction::dao()->updateBillLine($existsTransaction);
                    }

                    $existsTransaction->save();
                } else {
                    $transaction->save();
                }


            }

            foreach ($existsTransactionsByKey as $transactions) {
                foreach ($transactions as $transaction) {

                    if ($transaction->bill_id && $transaction->bill_line_id) {
                        if (!isset($tochedBillIds[$transaction->bill_id])) {
                            $tochedBillIds[$transaction->bill_id] = $transaction->bill_id;
                        }

                        Transaction::dao()->deleteBillLine($transaction);
                    }

                    $transaction->delete();
                }
            }

            foreach ($tochedBillIds as $billId) {
                $bill = Bill::findOne($billId);
                Bill::dao()->recalcBill($bill);
            }

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    public function createAndSaveBill()
    {
        /** @var Transaction[] $transactions */
        $transactions =
            Transaction::find()
                ->andWhere(['client_account_id' => $this->clientAccount->id, 'source' => Transaction::SOURCE_STAT])
                ->andWhere('transaction_date >= :from', [':from' => $this->billerPeriodFrom->format('Y-m-d H:i:s')])
                ->andWhere('transaction_date <= :to', [':to' => $this->billerPeriodTo->format('Y-m-d H:i:s')])
                ->andWhere('bill_id is null and deleted = 0')
                ->all();

        if (empty($transactions)) {
            return;
        }

        $sum = 0;
        foreach ($transactions as $transaction) {
            $sum += $transaction->sum;
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $bill = new Bill();
            $bill->client_id = $this->clientAccount->id;
            $bill->currency = $this->clientAccount->currency;
            $bill->nal = $this->clientAccount->nal;
            $bill->is_lk_show = 1;
            $bill->is_user_prepay = 0;
            $bill->is_approved = 1;
            $bill->is_use_tax = $this->clientAccount->nds_zero > 0 ? 0 : 1;
            $bill->bill_date = $this->billerPeriodFrom->format('Y-m-d');
            $bill->sum_with_unapproved = $sum;
            $bill->sum = $sum;
            $bill->bill_no = Bill::dao()->spawnBillNumber($this->billerPeriodFrom);
            $bill->save();

            $sort = 1;
            foreach ($transactions as $transaction) {
                Transaction::dao()->insertBillLine($transaction, $bill, $sort);
                $sort++;
            }

            Bill::dao()->recalcBill($bill);

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    private function processUsages(array $usages)
    {
        foreach ($usages as $usage) {
            $this->processUsage($usage);
        }
    }

    private function processUsage(Usage $usage)
    {
        try {

            $transactions =
                $usage
                    ->getBiller($this->billerDate)
                    ->process()
                    ->getTransactions();
            $this->transactions = array_merge($this->transactions, $transactions);

        } catch (\Exception $e) {
            $this->errors[] = [
                'usage' => $usage,
                'exception' => $e,
            ];
        }

    }

}