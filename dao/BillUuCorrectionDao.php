<?php

namespace app\dao;

use app\classes\HandlerLogger;
use app\classes\helpers\DependecyHelper;
use app\classes\Language;
use app\classes\model\ActiveRecord;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillCorrection;
use app\models\billing\CallsRaw;
use app\models\billing\Trunk;
use app\models\BillLine;
use app\models\BillLineUu;
use app\models\BillOwner;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\models\ClientDocument;
use app\models\Currency;
use app\models\Invoice;
use app\models\LogBill;
use app\models\OperationType;
use app\models\Organization;
use app\models\Payment;
use app\models\Transaction;
use app\models\UsageTrunk;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountEntryCorrection;
use app\modules\uu\models\Bill as uuBill;
use Yii;
use yii\caching\ChainedDependency;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\Expression;
use yii\db\Query;


/**
 * @method static BillUuCorrectionDao me($args = null)
 */
class BillUuCorrectionDao extends Singleton
{
    public function checkBill(Bill $bill)
    {
        $lines = $bill->getLines()
            ->indexBy('uu_account_entry_id')
            ->select('sum')
            ->column();

        $uuLines = $bill->getUuLines()
            ->select('sum')
            ->column();

        $corrSum = 0;
        foreach ($lines as $accountEntryId => $lineSum) {
            if (isset($uuLines[$accountEntryId])) {
                $uLine = $uuLines[$accountEntryId];
                $corrSum -= $lineSum - $uLine;
                unset($uuLines[$accountEntryId]);
            }
        }
        $corrSum += array_sum($uuLines);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->makeOrDeleteAccountEntryCorrection($bill, -$corrSum);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function makeOrDeleteAccountEntryCorrection(Bill $bill, $sum)
    {
        // удаление корректировок к счету
        if ($bill->correction_bill_id) {
            $corrBill = $bill->correctionBill;

            $bill->correction_bill_id = null;
            if (!$bill->save()) {
                throw new ModelValidationException($bill);
            }

            if ($corrBill) {
                $corrBill->isSkipCheckCorrection = true;
                if (!$corrBill->delete()) {
                    throw new ModelValidationException($corrBill);
                }
            }
        }

        $correction = $bill->accountEntryCorrection;

        if (abs($sum) < 0.01) {
            if ($correction) {
                $correction->delete();
            }
            return;
        }

        if (!$correction) {
            $correction = new AccountEntryCorrection();
            $correction->client_account_id = $bill->client_id;
            $correction->bill_no = $bill->bill_no;
        }

        $correction->sum = $sum;

        if (!$correction->save()) {
            throw new ModelValidationException($correction);
        }
    }
}
