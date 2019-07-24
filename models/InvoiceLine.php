<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;

/**
 * Строка в счёте-фактуре
 *
 * @property integer $pk
 * @property integer $invoice_id
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
 * @property-read Invoice $invoice
 */
class InvoiceLine extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'invoice_lines';
    }

    public function rules()
    {
        return [
            [['invoice_id', 'item', 'amount', 'price'], 'required'],
            [['sum_without_tax', 'sum_tax'], 'number'],
            ['type', 'default', 'value' => BillLine::LINE_TYPE_SERVICE],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    /**
     * Рассчитать все суммы до сохранения
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->calculateSum($this->invoice->bill->price_include_vat);

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

    /**
     * Дата счёта-фактуры в timestamp
     *
     * @throws \Exception
     */
    public function setDates()
    {
        $endDate = (new \DateTimeImmutable($this->invoice->bill->bill_date))
            ->modify('last day of ' . ($this->invoice->type_id == 1 ? 'this' : 'previous') . ' month');

        $startDate = $endDate->modify('first day of this month');

        $this->date_from = $startDate->format(DateTimeZoneHelper::DATE_FORMAT);
        $this->date_to = $endDate->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->sum_tax;
    }

    /**
     * @return float
     */
    public function getPrice_without_vat()
    {
        return $this->sum_without_tax;
    }

    /**
     * @return float
     */
    public function getPrice_with_vat()
    {
        return $this->sum;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getTypeUnitName()
    {
        return '';
    }

    /**
     * @return int
     */
    public function getVat_rate()
    {
        return $this->tax_rate;
    }

    /**
     * @return float
     */
    public function getOutprice()
    {
        return $this->price;
    }
}