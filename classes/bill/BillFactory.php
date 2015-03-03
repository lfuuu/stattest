<?php
namespace app\classes\bill;

use Yii;
use DateTime;
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


class BillFactory
{
    /** @var DateTime */
    protected $billerDate;
    /** @var DateTime */
    public $billerPeriodFrom;
    /** @var DateTime */
    public $billerPeriodTo;

    /** @var ClientAccount */
    protected $clientAccount;

    /**
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @return BillFactory
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

    /**
     * @return Bill|null
     */
    public function process()
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
            return null;
        }

        $sum = 0;
        foreach ($transactions as $transaction) {
            $sum += $transaction->sum;
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $bill =
                Bill::find()
                    ->andWhere(['client_id' => $this->clientAccount->id])
                    ->andWhere(['currency' => $this->clientAccount->currency])
                    ->andWhere('bill_date >= :dateFrom', [':dateFrom' => $this->billerPeriodFrom->format('Y-m-d')])
                    ->andWhere('bill_date <= :dateTo', [':dateTo' => $this->billerPeriodTo->format('Y-m-d')])
                    ->andWhere(['is_approved' => 1])
                    ->andWhere('bill_no like :billno', [':billno' => $this->billerPeriodFrom->format('Ym') . '-%'])
                    ->orderBy('bill_date asc, id asc')
                    ->limit(1)
                    ->one();
            if ($bill === null) {
                $bill = new Bill();
                $bill->client_id = $this->clientAccount->id;
                $bill->currency = $this->clientAccount->currency;
                $bill->nal = $this->clientAccount->nal;
                $bill->is_lk_show = 0;
                $bill->is_user_prepay = 0;
                $bill->is_approved = 1;
                $bill->is_use_tax = $this->clientAccount->nds_zero > 0 ? 0 : 1;
                $bill->bill_date = $this->billerPeriodFrom->format('Y-m-d');
                $bill->sum_with_unapproved = $sum;
                $bill->sum = $sum;
                $bill->bill_no = Bill::dao()->spawnBillNumber($this->billerPeriodFrom);
                $bill->save();
                $sort = 1;
            } else {
                $sort = count($bill->lines) + 1;
            }
            
            foreach ($transactions as $transaction) {
                Transaction::dao()->insertBillLine($transaction, $bill, $sort);
                $sort++;
            }

            Bill::dao()->recalcBill($bill);

            $dbTransaction->commit();

            return $bill;
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }
}