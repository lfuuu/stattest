<?php
namespace app\models;

use app\dao\ClientCounterDao;
use yii\db\ActiveRecord;

/**
 * @property int $client_id
 * @property float $amount_sum
 * @property float $amount_day_sum
 * @property float $amount_month_sum
 * @property float $subscription_rt_balance
 * @property float $subscription_rt_last_month
 * @property float $subscription_rt
 */
class ClientCounter extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_counters';
    }

    public static function dao()
    {
        return ClientCounterDao::me();
    }

}
