<?php

namespace app\models;

use app\classes\behaviors\InvoiceNextIdx;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;

/**
 * @property int $id
 * @property string $number
 * @property int $organization_id
 * @property string $bill_no
 * @property int $idx
 * @property int $type_id
 * @property string $date
 * @property float $sum
 * @property bool $is_reversal
 * @property string $add_date
 * @property string $reversal_date
 *
 * @property-read Bill bill
 */
class Invoice extends ActiveRecord
{
    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_GOOD = 3;
    const TYPE_PREPAID = 4;

    const DATE_ACCOUNTING = '2018-08-01';

    public static $types = [self::TYPE_1, self::TYPE_2, self::TYPE_GOOD];

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
     * @throws ModelValidationException
     */
    public function setReversal()
    {
        if ($this->is_reversal) {
            return;
        }

        $this->is_reversal = 1;
        $this->reversal_date = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }


}
