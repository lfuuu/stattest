<?php


namespace app\models\billing;

use app\models\billing\Counter;

/**
 * @property float $amount_sum
 * @property float $amount_day_sum
 * @property float $amount_mn_day_sum
 * @property float $amount_month_sum
 * @property string $amount_date
 */
class CachedCounter extends Counter
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.cached_counters';
    }
}