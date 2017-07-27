<?php

namespace app\models\voip;

use app\classes\model\ActiveRecord;

/**
 * Class StatisticMonth
 *
 * @property integer $account_id
 * @property string $date
 * @property integer $count
 * @property integer $cost
 */
class StatisticMonth extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'stat_voip_month';
    }
}
