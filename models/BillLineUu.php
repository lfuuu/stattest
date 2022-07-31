<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property integer uu_account_entry_id
 * @property string bill_no
 * @property string item
 * @property float amount
 * @property float price
 * @property float sum
 * @property integer id_service
 * @property string type
 * @property float tax_rate
 * @property float sum_without_tax
 * @property float sum_tax
 * @property float cost_price
 *
 * @property-read Bill $bill
 */
class BillLineUu extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'newbill_lines_uu';
    }

    /**
     * @inheritdoc $primaryKey
     */
    public static function primaryKey()
    {
        return ["uu_account_entry_id"];
    }

    public function rules()
    {
        return [
            [['bill_no', 'uu_account_entry_id', 'item', 'amount', 'price'], 'required'],
            [['tax_rate', 'amount', 'price', 'sum', 'sum_without_tax', 'sum_tax', 'id_service', 'cost_price', 'sort', 'discount_auto', 'discount_set'], 'number'],
            [['date_from', 'date_to', 'service'], 'string'],
            ['type', 'default', 'value' => BillLine::LINE_TYPE_SERVICE],
        ];
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }
}