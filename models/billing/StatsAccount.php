<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $account_id
 * @property int $server_id
 * @property string $amount_month
 * @property float $sum_month
 * @property string $amount_day
 * @property float $sum_day
 * @property string $amount_date
 * @property float $sum
 * @property float $sum_mn_day
 */
class StatsAccount extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.stats_account';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }
}
