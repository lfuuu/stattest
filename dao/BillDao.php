<?php
namespace app\dao;

use app\classes\Singleton;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\Bill as uuBill;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\BillOwner;
use app\models\ClientAccount;
use app\models\Transaction;
use LogicException;
use Yii;


/**
 * @method static BillDao me($args = null)
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
            $lines = $bill->getLines()->all();

            $this->calculateBillSum($bill, $lines);

            if ($bill->biller_version === null || $bill->biller_version == ClientAccount::VERSION_BILLER_USAGE) {
                $this->updateTransactions($bill, $lines);
            }

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

        if ($bill->is_rollback && $bill->sum_with_unapproved > 0) {
            $bill->sum_with_unapproved = -$bill->sum_with_unapproved;
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

        } elseif (preg_match("/20\d{4}-\d{4}/", $bill_no) || preg_match("/[4567]\d{5}/", $bill_no)) {
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

    public function setManager($billNo, $userId)
    {
        $owner = BillOwner::findOne($billNo);
        if ($userId) {
            if ($owner == null) {
                $owner = new BillOwner();
            }
            $owner->bill_no = $billNo;
            $owner->owner_id = $userId;
            $owner->save();
        } elseif ($owner) {
            //$owner->delete();
        }
    }


    /**
     * Функция переноса проводок универсального биллера в "старые" счета
     * @param uuBill $uuBill
     * @throws \Exception
     */
    public function transferUniversalBillsToBills(uuBill $uuBill)
    {
        $bill = Bill::find()
            ->where(['uu_bill_id' => $uuBill->id])
            ->one();

        if (!$uuBill->price) {
            // нулевые счета не нужны
            if ($bill && !$bill->delete()) {
                throw new LogicException(implode(' ', $bill->getFirstErrors()));
            }
            return false;
        }

        $clientAccount = $uuBill->clientAccount;

        $uuBillDateTime = new \DateTimeImmutable($uuBill->date);
        $newBillNo = $uuBillDateTime->format('ym') . $uuBill->id;

        if (!$bill) {
            $bill = new Bill();
            $bill->client_id = $clientAccount->id;
            $bill->currency = $clientAccount->currency;
            $bill->nal = $clientAccount->nal;
            $bill->is_lk_show = 0;
            $bill->is_user_prepay = 0;
            $bill->is_approved = 1;
            $bill->bill_date = $uuBillDateTime->format(DateTimeZoneHelper::DATE_FORMAT);
            $bill->sum_with_unapproved = $uuBill->price;
            $bill->price_include_vat = $clientAccount->price_include_vat;
            $bill->sum = $uuBill->price;
            $bill->bill_no = $newBillNo;
            $bill->biller_version = ClientAccount::VERSION_BILLER_UNIVERSAL;
            $bill->uu_bill_id = $uuBill->id;
            if (!$bill->save()) {
                throw new LogicException(implode(' ', $bill->getFirstErrors()));
            }
        } elseif ($bill->bill_no != $newBillNo) {
            $bill->bill_no = $newBillNo;
            if (!$bill->save()) {
                throw new LogicException(implode(' ', $bill->getFirstErrors()));
            }
        }

        $toRecalculateBillSum = false;
        $billLinePosition = 0;

        // новые проводки
        /** @var AccountEntry[] $accountEntries */
        $accountEntries = $uuBill
            ->getAccountEntries()
            ->andWhere(['>', 'price', 0]) // пустые строки нужны для расчета партнерского вознаграждения
            ->orderBy(['id' => SORT_ASC])
            ->all();

        // старые проводки
        $lines = $bill->getLines()
            ->indexBy('uu_account_entry_id')
            ->all();

        /** @var BillLine $line */
        foreach ($lines as $accountEntryId => $line) {

            if (!isset($accountEntries[$accountEntryId])) {
                // была, но сейчас нет. Удалить
                $line->delete();
                continue;
            }

            // была и осталась
            $accountEntry = $accountEntries[$accountEntryId];
            if ((float)$line->sum != $accountEntry->price_with_vat || $line->item != $accountEntry->fullName) {
                // ... но изменилась. Обновить
                $line->sum = $accountEntry->price_with_vat;
                $line->item = $accountEntry->fullName;
                $line->save();

                $toRecalculateBillSum = true;
            }
            unset($accountEntries[$accountEntryId]);
            $billLinePosition = max($billLinePosition, $line->sort);
        }
        unset($lines, $line);

        // не было, но стало. Добавить
        foreach ($accountEntries as $accountEntry) {

            $billLinePosition++;

            $line = new BillLine();
            $line->sort = $billLinePosition;
            $line->bill_no = $bill->bill_no;

            $line->item = $accountEntry->fullName;
            $line->date_from = $accountEntry->date_from;
            $line->date_to = $accountEntry->date_to;
            $line->type = BillLine::LINE_TYPE_SERVICE;
            $line->amount = 1;
            $line->price = $accountEntry->price_with_vat;
            $line->tax_rate = $accountEntry->vat;
            $line->sum = $accountEntry->price_with_vat;
            $line->sum_without_tax = $accountEntry->price_without_vat;
            $line->sum_tax = $accountEntry->vat;
            $line->uu_account_entry_id = $accountEntry->id;
            $line->service = 'uu_account_tariff';
            $line->id_service = $accountEntry->account_tariff_id;
            $line->item_id = $accountEntry->accountTariff->getNonUniversalId();
            if (!$line->save()) {
                throw new LogicException(implode(' ', $line->getFirstErrors()));
            }

            $toRecalculateBillSum = true;
        }

        if ($toRecalculateBillSum) {
            Bill::dao()->recalcBill($bill);
        }

        $uuBill->is_converted = 1;
        if (!$uuBill->save()) {
            throw new LogicException(implode(' ', $uuBill->getFirstErrors()));
        }
    }
}
