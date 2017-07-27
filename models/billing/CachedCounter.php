<?php


namespace app\models\billing;

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