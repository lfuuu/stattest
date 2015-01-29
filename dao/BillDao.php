<?php
namespace app\dao;

use app\models\BillOwner;
use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\Bill;
use app\models\BillLine;
use app\models\Transaction;

/**
 * @method static BillDao me($args = null)
 * @property
 */
class BillDao extends Singleton
{
    public function spawnBillNumber($billDate)
    {
        if ($billDate instanceof \DateTime) {
            $prefix = $billDate->format('Ym');
        } else {
            $prefix = substr($billDate, 0, 4) . substr($billDate, 5, 2);
        }

        $lastBillNumber =
            Bill::find()
                ->select('bill_no')
                ->andWhere('bill_no like :prefix', [':prefix' => $prefix . '-%'])
                ->orderBy('bill_no desc')
                ->limit(1)
                ->scalar();

        if ($lastBillNumber) {
            $suffix = 1 + intval(substr($lastBillNumber, 7));
        } else {
            $suffix = 1;
        }

        return sprintf("%s-%04d", $prefix, $suffix);
    }

    public function recalcBill(Bill $bill)
    {
        $dbTransaction = Bill::getDb()->beginTransaction();
        try {
            $this->calculateBillSum($bill, $bill->lines);

            $this->updateTransactions($bill, $bill->lines);

            $bill->save();

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    private function calculateBillSum(Bill $bill, array $lines)
    {
        /** @var BillLine[] $lines */

        $bill->sum_with_unapproved = 0;
        foreach ($lines as $line) {
            if ($line->type == 'zadatok') {
                continue;
            }
            $bill->sum_with_unapproved += $line->sum;
        }

        $bill->sum =
            $bill->is_approved
                ? $bill->sum_with_unapproved
                : 0;
    }

    private function updateTransactions(Bill $bill, array $lines)
    {
        $transactions = Transaction::find()->andWhere(['bill_id' => $bill->id])->indexBy('bill_line_id')->all();

        if ($bill->is_approved) {
            /** @var BillLine[] $lines */
            foreach ($lines as $line) {
                if ($line->type == 'zadatok') {
                    continue;
                }

                if (isset($transactions[$line->pk])) {

                    Transaction::dao()->updateByBillLine($bill, $line, $transactions[$line->pk]);
                    unset($transactions[$line->pk]);

                } else {
                    Transaction::dao()->insertByBillLine($bill, $line);
                }
            }
        }

        foreach ($transactions as $transaction) {
            if ($transaction->source == Transaction::SOURCE_STAT) {
                Transaction::dao()->markDeleted($transaction);
            } else {
                $transaction->delete();
            }
        }
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

    public function isClosed(Bill $bill)
    {
        $stateId =
            Yii::$app->db->createCommand('
                    SELECT state_id
                    FROM tt_troubles t, tt_stages s
                    WHERE bill_no = :billNo and  t.cur_stage_id = s.stage_id
                ', [':billNo' => $bill->bill_no]
            )->queryScalar();
        return $stateId == 20;
    }

    public function getManager($billNo)
    {
        return
            BillOwner::find()
                ->select('owner_id')
                ->andWhere(['bill_no' => $billNo])
                ->scalar();
    }

    public function setManager($billNo, $userId) {
        $owner = BillOwner::findOne($billNo);
        if ($userId) {
            if ($owner == null) {
                $owner = new BillOwner();
            }
            $owner->bill_no = $billNo;
            $owner->owner_id = $userId;
            $owner->save();
        } elseif ($owner) {
            $owner->delete();
        }
    }
}