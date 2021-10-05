<?php

namespace app\models\rewards;

use app\classes\model\ActiveRecord;
use app\models\Bill;

/**
 * Счет вознаграждения
 * 
 * @property int $id
 * @property int $bill_id
 * @property int $partner_id
 * @property string $payment_date
 * @property double $sum
 * 
 * @property-read RewardBillLine[] $lines
 */
class RewardBill extends ActiveRecord
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['bill_id', 'partner_id', 'payment_date'], 'required'],
            [['bill_id', 'partner_id'], 'integer'],
            [['payment_date'], 'string'],
            [['sum'], 'double'],
        ];
    }

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'reward_bill';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(RewardBillLine::class, ['bill_id' => 'bill_id'])->indexBy('bill_line_pk');
    }

    public function getBill()
    {
        return $this->hasOne(Bill::class, ['id' => 'bill_id']);
    }
}