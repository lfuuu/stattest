<?php

namespace app\dao;

use app\classes\Singleton;
use yii\db\ActiveQuery;
use yii\db\Expression;

class MonitoringDao extends Singleton
{

    /**
     * @param string $usage
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