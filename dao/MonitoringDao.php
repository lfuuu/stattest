<?php

namespace app\dao;

use app\classes\Singleton;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @method static MonitoringDao me($args = null)
 * @property
 */
class MonitoringDao extends Singleton
{

    /**
     * @param string $usage - className()
     * @return ActiveQuery
     */
    public static function transferedUsages($usage)
    {
        return
            $usage::find()
                ->where(['!=', 'prev_usage_id', 0])
                ->andWhere(['>', 'actual_from', new Expression('CAST(NOW() AS DATE)')])
                ->all();
    }

}