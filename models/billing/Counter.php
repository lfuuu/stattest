<?php

namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property float amount_sum
 * @property float amount_day_sum
 * @property float amount_mn_day_sum
 * @property float amount_month_sum
 * @property string amount_date
 */
class Counter extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.counters';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return $this
     */
    public static function find()
    {
        $query = parent::find();

        return
            $query->addSelect([
                'amount_sum' => 'CAST(amount_sum AS NUMERIC(10,2))',
                'amount_day_sum' => 'CAST(amount_day_sum AS NUMERIC(10,2))',
                'amount_mn_day_sum' => 'CAST(amount_mn_day_sum AS NUMERIC(10,2))',
                'amount_month_sum' => 'CAST(amount_month_sum AS NUMERIC(10,2))',
                'amount_date' => 'amount_date'
            ]);
    }

}
