<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

class Counter extends ActiveRecord
{

    public static function tableName()
    {
        return 'billing.counters';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function find()
    {
        $query = parent::find();
        $query->select([
            'CAST(amount_sum as NUMERIC(8,2)) as amount_sum',
            'CAST(amount_day_sum as NUMERIC(8,2)) as amount_day_sum',
            'CAST(amount_month_sum as NUMERIC(8,2)) as amount_month_sum'
        ]);
        return $query;
    }

}