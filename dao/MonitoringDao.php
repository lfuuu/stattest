<?php

namespace app\dao;

use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use yii\db\Expression;
use yii\db\Query;

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
                ->select('to_usage.*')
                ->from(['to_usage' => $usage::tableName()])
                ->where(['!=', 'to_usage.prev_usage_id', 0])
                ->andWhere(['>', 'to_usage.actual_from', new Expression('CAST(NOW() AS DATE)')]);

        if (!is_null($clientAccount)) {
            /** @var UsageInterface $emptyUsage */
            $emptyUsage = new $usage;

            list($usageField, $clientAccountField) = $emptyUsage->helper->fieldsForClientAccountLink;

            $query->innerJoin(['from_usage' => $usage::tableName()], 'from_usage.id = to_usage.prev_usage_id');
            $query->andWhere(['from_usage.' . $usageField => $clientAccount->{$clientAccountField}]);
        }

        return $query->all();
    }

}