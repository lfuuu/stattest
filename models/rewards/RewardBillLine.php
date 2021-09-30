<?php

namespace app\models\rewards;

use app\classes\model\ActiveRecord;

/**
 * Линии счета вознаграждения
 * 
 * @property int $id
 * @property int $bill_id
 * @property int $bill_line_pk
 * @property string $log
 * @property double $sum
 */
class RewardBillLine extends ActiveRecord
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['bill_id', 'bill_line_pk'], 'required'],
            [['bill_id', 'bill_line_pk'], 'integer'],
            [['sum'], 'double'],
            [['log'], 'string'],
        ];
    }

    public static function tableName()
    {
        return 'reward_bill_line';
    }

}