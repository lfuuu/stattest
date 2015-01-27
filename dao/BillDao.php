<?php
namespace app\dao;

use app\classes\bill\SubscriptionCalculator;
use app\classes\Singleton;
use app\classes\Utils;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\CurrencyRate;
use app\models\LogBill;
use app\models\Payment;
use app\models\User;

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

    public function recalcBill(Bill $bill)
    {
        $this->recalcBillLines($bill);
        $this->recalcBillSum($bill);
    }

    public function recalcBillSum(Bill $bill)
    {
        $bill->sum_with_unapproved =
            BillLine::find()
                ->andWhere(['bill_no' => $bill->bill_no])
                ->andWhere("type <> 'zadatok'")
                ->sum('sum');

        if ($bill->sum_with_unapproved === null) {
            $bill->sum_with_unapproved = 0;
        }

        $bill->sum =
            $bill->is_approved
                ? $bill->sum_with_unapproved
                : 0;

        if (isset($bill->dirtyAttributes['sum'])) {
            $log = new LogBill();
            $log->bill_no = $bill->bill_no;
            $log->ts = $bill->bill_no;
            $log->user_id = \Yii::$app->user->getId() ? \Yii::$app->user->getId() : User::SYSTEM_USER_ID;
            $log->comment = 'Сумма: ' . $bill->sum;
            $log->save();
        }

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

    public function getDocumentType($bill_no)
    {
        if (preg_match("/\d{2}-\d{8}/", $bill_no)) {

            return ['type' => 'incomegood'];

        } elseif (preg_match("/20\d{4}\/\d{4}/", $bill_no)) {

            return ['type' => 'bill', 'bill_type' => '1c'];

        } elseif (preg_match("/20\d{4}-\d{4}/", $bill_no) || preg_match("/[4567]\d{5}/", $bill_no)){
            // mcn telekom || all4net

            return ['type' => 'bill', 'bill_type' => 'stat'];

        }

        return ['type' => 'unknown'];
    }
}