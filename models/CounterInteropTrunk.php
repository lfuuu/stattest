<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class CounterInteropTrunk
 * 
 * @property int account_id
 * @property float income_sum
 * @property float outcome_sum
 * @package app\models
 */
class CounterInteropTrunk extends ActiveRecord
{

    public static function tableName()
    {
        return 'counter_interop_trunk';
    }

}
