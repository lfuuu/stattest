<?php

namespace app\dao;

use app\classes\Singleton;
use app\models\ClientAccount;
use yii\db\ActiveQuery;
use yii\db\Expression;

class MonitoringDao extends Singleton
{

    /**
     * @param string $usage
     * @param ClientAccount|null $clientAccount
     * @return mixed
     */
    public static function transferedUsages($usage, ClientAccount $clientAccount = null)
    {
        $query =
            $usage::find()
                ->where(['!=', 'prev_usage_id', 0])
                ->andWhere(['>', 'actual_from', new Expression('CAST(NOW() AS DATE)')]);

        if (!is_null($clientAccount)) {
            list($usageField, $clientAccountField) = $usage::getClientAccountLink();
            $query->andWhere([$usageField => $clientAccount->{$clientAccountField}]);
        }

        return $query->all();
    }

}