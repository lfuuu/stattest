<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property integer $pk
 * @property integer $bill_correction_id
 * @property string $bill_no
 * @property integer $sort
 * @property string $item
 * @property float $amount
 * @property float $price
 * @property float $sum
 * @property string $date_from
 * @property string $date_to
 * @property string $type
 * @property integer $tax_rate
 * @property float $sum_without_tax
 * @property float $sum_tax
 *
 * @property-read Bill $bill
 */
class BillLineCorrection extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'newbill_lines_correction';
    }

    public function rules()
    {
        return [
            [['bill_correction_id', 'bill_no', 'item', 'amount', 'price'], 'required'],
            [['sum_without_tax', 'sum_tax'], 'number'],
            ['type', 'default', 'value' => BillLine::LINE_TYPE_SERVICE],
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
    public function getBillCorrection()
    {
        return $this->hasOne(BillCorrection::class, ['id' => 'bill_correction_id']);
    }

    /**
     * Рассчитать все суммы до сохранения
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->calculateSum($this->bill->price_include_vat);

        return parent::beforeSave($insert);
    }

    /**
     * @param boolean $priceIncludeVat
     */
    public function calculateSum($priceIncludeVat)
    {
        $sum = $this->price * $this->amount;

        if ($priceIncludeVat) {
            $this->sum = round($sum, 2);
            $this->sum_tax = round($this->tax_rate / (100.0 + $this->tax_rate) * $this->sum, 2);
            $this->sum_without_tax = $this->sum - $this->sum_tax;
        } else {
            $this->sum_without_tax = round($sum, 2);
            $this->sum_tax = round($this->sum_without_tax * $this->tax_rate / 100, 2);
            $this->sum = $this->sum_without_tax + $this->sum_tax;
        }
    }

}