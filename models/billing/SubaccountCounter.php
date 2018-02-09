<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use yii;

/**
 * @property int $subaccount_id
 * @property int $client_account_id
 * @property float $amount_month_sum
 * @property float $amount_day_sum
 * @property float $amount_mn_day_sum
 * @property float $amount_sum
 * @property string $amount_date
 */
class SubaccountCounter extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.counters_subaccount';
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
}
