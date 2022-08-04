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
            $this->makeOrDeleteCorrectionBill($bill, $corrSum);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function makeOrDeleteCorrectionBill(Bill $bill, $sum)
    {
        if (abs($sum) < 0.001) {

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

            return;
        }

        list($corrBill, $line) = $this->getCorrectionBillAndLine($bill);

        /** @var BillLine sum */
        $line->sum = $sum;

        /** @var Bill sum */
        $corrBill->sum = $sum;

        if (!$line->save()) {
            throw new ModelValidationException($corrBill);
        }

        if (!$corrBill->save()) {
            throw new ModelValidationException($bill);
        }

    }

    private function getCorrectionBillAndLine($bill)
    {
        if (!$bill->correction_bill_id) {
            $corrBill = Bill::dao()->createBill($bill->clientAccount, $bill->currency, true);

            $corrBill->operation_type_id = OperationType::ID_CORRECTION;
            $corrBill->comment = 'Автоматическая корректировка к счету ' . $bill->bill_no;
            $bill->correction_bill_id = $corrBill->id;
            $corrBill->price_include_vat = 1; // здесь сумма конечная, всегда с НДС

            if (!$bill->save()) {
                throw new ModelValidationException($bill);
            }


            $lineItem = \Yii::t(
                'biller',
                'correct_sum',
                [],
                \app\classes\Language::normalizeLang($bill->clientAccount->contract->contragent->lang_code)
            );

            $line = $corrBill->addLine(
                $lineItem,
                1,
                0,
                BillLine::LINE_TYPE_SERVICE
            );

            return [$corrBill, $line];
        }

        $corrBill = $bill->correctionBill;
        $lines = $corrBill->lines;

        $line = reset($lines);

        return [$corrBill, $line];

    }
}
