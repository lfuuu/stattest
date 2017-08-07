<?php

namespace app\dao;

use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use app\modules\uu\models\AccountTariff;
use yii\db\ActiveRecord;
use yii\db\Expression;

class MonitoringDao extends Singleton
{

    /**
     * @param string $serviceName
     * @param ClientAccount|null $clientAccount
     * @return ActiveRecord[]|null
     */
    public static function transferredRegularServices($serviceName, ClientAccount $clientAccount = null)
    {
        $serviceTableName = $serviceName::tableName();
        $query = $serviceName::find()
            ->where([
                'AND',
                ['!=', $serviceTableName . '.prev_usage_id', 0],
                ['>', $serviceTableName . '.actual_from', new Expression('CAST(NOW() AS DATE)')]
            ])
            ->orWhere(['>', 'next_usage_id', AccountTariff::DELTA]);

        if (!is_null($clientAccount)) {
            /** @var UsageInterface $emptyUsage */
            $emptyUsage = new $serviceName;

            list($usageField, $clientAccountField) = $emptyUsage->helper->fieldsForClientAccountLink;

            $query->innerJoin(
                ['sourceService' => $serviceName::tableName()],
                'sourceService.id = ' . $serviceTableName . '.prev_usage_id'
            );
            $query->andWhere(['sourceService.' . $usageField => $clientAccount->{$clientAccountField}]);
        }

        return $query->all();
    }

    /**
     * @param int $serviceTypeId
     * @param ClientAccount|null $clientAccount
     * @return ActiveRecord[]|null
     */
    public static function transferredUniversalServices($serviceTypeId, ClientAccount $clientAccount = null)
    {
        $query = AccountTariff::find()
            ->where(['!=', 'prev_usage_id', 0])
            ->andWhere(['service_type_id' => $serviceTypeId]);

        if (!is_null($clientAccount)) {
            $query->andWhere(['client_account_id' => $clientAccount->id]);
        }

        return $query->all();
    }

}