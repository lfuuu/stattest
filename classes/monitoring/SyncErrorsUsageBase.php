<?php

namespace app\classes\monitoring;

use app\classes\DBROQuery;
use app\models\ClientAccount;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

abstract class SyncErrorsUsageBase extends Component implements MonitoringInterface
{

    const LIMIT_DEFAULT = 100000;

    const STATUS_IN_STAT = 'in_stat_only';
    const STATUS_ACCOUNT_DIFF = 'account_different';
    const STATUS_IN_PLATFORM = 'in_platform_only';

    public static $statusNames = [
        self::STATUS_IN_STAT => 'Только на стате',
        self::STATUS_ACCOUNT_DIFF => 'Разные ЛС',
        self::STATUS_IN_PLATFORM => 'Только на платформе',
    ];

    public static $statusClasses= [
        self::STATUS_IN_STAT => 'text-warning',
        self::STATUS_ACCOUNT_DIFF => 'text-danger',
        self::STATUS_IN_PLATFORM => 'text-info',
    ];

    abstract public function getServiceType();

    public function getServiceIdField()
    {
        return "id";
    }


    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $cacheId = 'monitor_' . $this->getKey();

        $result = [];

        if (Yii::$app->request->get('page') && Yii::$app->cache->exists($cacheId))
        {
            $result = Yii::$app->cache->get($cacheId);
        } else {

            $dbroResult = ArrayHelper::map((new DBROQuery())
                ->select(["service_id", "account_id"])
                ->from('services_available')
                ->where([
                    'service_type' => $this->getServiceType(),
                    'enabled' => 't'
                ])
                ->limit(self::LIMIT_DEFAULT)
                ->all(),
                "service_id",
                "account_id"
            );

            $serviceClass = $this->getServiceClass();

            $statResult = ArrayHelper::map(
                $serviceClass::find()
                    ->select(['id' => 'u.' . $this->getServiceIdField(), 'client_id' => 'c.id'])
                    ->from(['u' => $serviceClass::tableName()])
                    ->actual()
                    ->innerJoin(['c' => ClientAccount::tableName()], 'c.client = u.client')
                    ->limit(self::LIMIT_DEFAULT)
                    ->createCommand()->queryAll(),
                'id',
                'client_id'
            );

            $dbroKeys = array_keys($dbroResult);
            $statKeys = array_keys($statResult);

            foreach (array_diff($dbroKeys, $statKeys) as $dbroUsageId) {
                $result[$dbroUsageId] = [
                    'usage_id' => $dbroUsageId,
                    'account_id' => $dbroResult[$dbroUsageId],
                    'status' => self::STATUS_IN_PLATFORM
                ];
            }

            foreach (array_diff($statKeys, $dbroKeys) as $statUsageId) {
                $result[$statUsageId] = [
                    'usage_id' => $statUsageId,
                    'account_id' => $statResult[$statUsageId],
                    'status' => self::STATUS_IN_STAT
                ];
            }

            foreach (array_intersect($dbroKeys, $statKeys) as $usageId) {
                if ($dbroResult[$usageId] != $statResult[$usageId]) {
                    $result[$usageId] = [
                        'usage_id' => $usageId,
                        'account_id' => $statResult[$usageId],
                        'account_id2' => $dbroResult[$usageId],
                        'status' => self::STATUS_ACCOUNT_DIFF
                    ];
                }
            }

            ksort($result);
            Yii::$app->cache->set($cacheId, $result);
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}