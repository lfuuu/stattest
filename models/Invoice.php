<?php

namespace app\models;

use app\classes\behaviors\InvoiceNextIdx;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

/**
 * @property int $id
 * @property string $number
 * @property int $organization_id
 * @property string $bill_no
 * @property int $idx
 * @property int $type_id
 * @property string $date
 * @property float $sum
 * @property float $sum_tax
 * @property float $sum_without_tax
 * @property bool $is_reversal
 * @property string $add_date
 * @property string $reversal_date
 *
 * @property-read Bill $bill
 * @property-read InvoiceLine $lines
 * @property-read Organization $organization
 */
class Invoice extends ActiveRecord
{
    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_GOOD = 3;
    const TYPE_PREPAID = 4;

    const DATE_ACCOUNTING = '2018-08-01';
    const DATE_NO_RUSSIAN_ACCOUNTING = '2019-01-01';

    public static $types = [self::TYPE_1, self::TYPE_2, self::TYPE_GOOD];

    // Создается draft
    public $isSetDraft = null;

    public $isAsInsert = false;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'InvoiceNextIdx' => InvoiceNextIdx::class,
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(InvoiceLine::class, ['invoice_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['organization_id' => 'id']);
    }

    public function getDateImmutable()
    {
        return (new \DateTimeImmutable($this->date));
    }

    /**
     * Получает даты по типу
     *
     * @param Bill $bill
     * @param int $typeId
     * @return \DateTimeImmutable
     */
    public static function getDate(Bill $bill, $typeId)
    {
        $date = new \DateTimeImmutable($bill->bill_date);

        switch ($typeId) {
            case self::TYPE_1:
                return $date->modify('last day of this month');
                break;

            case self::TYPE_2:
                return $date->modify('last day of previous month');
                break;

            case self::TYPE_GOOD:
                return self::_getBillWithGoodDate($bill, $date);
                break;

            case self::TYPE_PREPAID:
                return self::_getBillPaymentDate($bill);
                break;

            default:
                return $date;
        }
    }

    /**
     * Дата первого платежа для с/ф 4
     *
     * @param Bill $bill
     * @return bool|\DateTimeImmutable
     */
    public static function _getBillPaymentDate(Bill $bill)
    {
        /** @var Payment $payment */
        $payment = Payment::find()
            ->where(['bill_no' => $bill->bill_no])
            ->orderBy(['id' => SORT_ASC])
            ->one();

        if ($payment) {
            return (new \DateTimeImmutable($payment->payment_date));
        }

        return false;
    }

    /**
     * @param Bill $bill
     * @param $defaultDate
     * @return bool|\DateTimeImmutable
     */
    private static function _getBillWithGoodDate(Bill $bill, $defaultDate)
    {
        if (!$bill->is1C()) {
            return $defaultDate;
        }

        if ($bill->doc_date && $bill->doc_date != '0000-00-00') {
            return (new \DateTimeImmutable())->setTimestamp($bill->doc_date);
        }

        $date = self::_getShippedDateFromTrouble($bill);

        if ($date) {
            return $date;
        }

        return false;
    }

    /**
     * @param Bill $bill
     * @return bool|\DateTimeImmutable
     */
    private static function _getShippedDateFromTrouble(Bill $bill)
    {
        $value = \Yii::$app->db->createCommand("
                     SELECT 
                        min(cast(date_start AS DATE))
                     FROM 
                        tt_troubles t , `tt_stages` s  
                     WHERE 
                            t.bill_no = :bill_no
                        AND t.id = s.trouble_id 
                        AND state_id IN (SELECT id FROM tt_states WHERE state_1c = 'Отгружен')
                        ", [":bill_no" => $bill->bill_no])
            ->queryScalar();

        if ($value) {
            return (new \DateTimeImmutable($value));
        }

        return false;
    }

    /**
     * @param bool $isRevertSum
     * @throws ModelValidationException
     */
    public function setReversal($isRevertSum = false)
    {
        if ($this->is_reversal) {
            return;
        }

        $this->is_reversal = 1;
        $this->reversal_date = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if ($isRevertSum) {
            $this->sum = -$this->sum;
            $this->sum_without_tax = -$this->sum_without_tax;
            $this->sum_tax = -$this->sum_tax;
        }

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }

    public static function getInfo($billNo)
    {
        $bill = Bill::findOne(['bill_no' => $billNo]);

        if (!$bill) {
            throw new NotFoundHttpException('Bill not found');
        }

        $info = [];
        foreach (array_merge(self::$types, [self::TYPE_PREPAID]) as $typeId) {
            if ($typeInfo = self::_getInfoByType($bill, $typeId)) {
                $info[$typeId] = $typeInfo;
            }
        }

        return $info;
    }

    public function _getInfoByType(Bill $bill, $typeId)
    {
        $lines = $bill->getLinesByTypeId($typeId);

        // @TODO
        // проверка - можно ли по этому счету выписать авансовую с/ф

        // нет проводок - нет документа. Кроме авансовой с/ф
        if ($typeId != self::TYPE_PREPAID && !$lines) {
            return false;
        }

        $info = [
            'status' => 'empty',
            'invoices' => [],
        ];

        /** @var Invoice $invoice */
        foreach (Invoice::find()
                     ->where(['bill_no' => $bill->bill_no, 'type_id' => $typeId])
                     ->orderBy(['id' => SORT_ASC])
                     ->all()
                 as $invoice) {
            $info['invoices'][] = $invoice;

            if ($invoice->is_reversal) {
                $info['status'] = 'reversal';
            } elseif (!$invoice->idx) {
                $info['status'] = 'draft';
            } else {
                $info['status'] = 'invoice';
                $info['stornoId'] = $invoice->id;
            }
            $info['lastId'] = $invoice->id;
        }

        return $info;
    }

    public function afterSave($isInsert, $changedAttributes)
    {
        if (
            (!$isInsert && !$this->isAsInsert)
//            || $this->bill->clientAccount->country_id == Country::RUSSIA
            || $this->bill->bill_date < '2019-02-01'
        ) {
            return;
        }

        foreach ($this->bill->getLinesByTypeId($this->type_id, $isInsert) as $line) {
            $newLine = new InvoiceLine();

            if ($line instanceof BillLine) {
                $data = $line->getAttributes(null, ['pk']);
            } else {
                // array
                $data = $line;
                unset($data['pk']);
            }

            $newLine->setAttributes($data, false);
            $newLine->invoice_id = $this->id;

            if ($this->is_reversal) {
                $newLine->price = -$newLine->price;
                $newLine->sum = -$newLine->sum;
                $newLine->sum_tax = -$newLine->sum_tax;
                $newLine->sum_without_tax = -$newLine->sum_without_tax;
            }

            if (!$newLine->save()) {
                throw new ModelValidationException($newLine);
            }
        }
    }

    public function recalcSumCorrection()
    {
        $sums = InvoiceLine::find()
            ->where(['invoice_id' => $this->id])
            ->select([
                'sum' => (new Expression('SUM(sum)')),
                'sum_tax' => (new Expression('SUM(sum_tax)')),
                'sum_without_tax' => (new Expression('SUM(sum_without_tax)')),
            ])
            ->asArray()
            ->one();

        $this->sum = $sums['sum'];
        $this->sum_tax = $sums['sum_tax'];
        $this->sum_without_tax = $sums['sum_without_tax'];

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }
}
