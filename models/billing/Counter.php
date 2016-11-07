<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property float amount_sum
 * @property float amount_day_sum
 * @property float amount_month_sum
 */
class Counter extends ActiveRecord
{

    public static function tableName()
    {
        return 'billing.counters';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function find()
    {
        $query = parent::find();

        return
            $query->addSelect([
                'amount_sum' => 'CAST(amount_sum AS NUMERIC(10,2))',
                'amount_day_sum' => 'CAST(amount_day_sum AS NUMERIC(10,2))',
                'amount_month_sum' => 'CAST(amount_month_sum AS NUMERIC(10,2))'
            ]);
    }

}
