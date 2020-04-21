<?php


namespace app\models\billing;

/**
 * @property float $sum_w_neg_rate
 * @property float $sum_w_neg_rate_day
 * @property float $sum_w_neg_rate_month
 * @property float $voice_sum_day
 * @property float $data_sum_day
 * @property float $sms_sum_day
 * @property float $voice_sum_month
 * @property float $data_sum_month
 * @property float $sms_sum_month
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

    /**
     * @return $this
     */
    public static function find()
    {
        $query = parent::find();

        return
            $query->addSelect([
                'sum_w_neg_rate' => 'CAST(sum_w_neg_rate AS NUMERIC(10,2))',
                'sum_w_neg_rate_day' => 'CAST(sum_w_neg_rate_day AS NUMERIC(10,2))',
                'sum_w_neg_rate_month' => 'CAST(sum_w_neg_rate_month AS NUMERIC(10,2))',
                'voice_sum_day' => 'CAST(voice_sum_day AS NUMERIC(10,2))',
                'voice_sum_month' => 'CAST(voice_sum_month AS NUMERIC(10,2))',
                'data_sum_day' => 'CAST(data_sum_day AS NUMERIC(10,2))',
                'data_sum_month' => 'CAST(data_sum_month AS NUMERIC(10,2))',
                'sms_sum_day' => 'CAST(sms_sum_day AS NUMERIC(10,2))',
                'sms_sum_month' => 'CAST(sms_sum_month AS NUMERIC(10,2))'
            ]);
    }
}