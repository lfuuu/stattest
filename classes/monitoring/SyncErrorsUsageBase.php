<?php

namespace app\classes\monitoring;

use app\classes\helpers\DependecyHelper;
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

    public static $statusClasses = [
        self::STATUS_IN_STAT => 'text-warning',
        self::STATUS_ACCOUNT_DIFF => 'text-danger',
        self::STATUS_IN_PLATFORM => 'text-info',
    ];

    abstract public function getServiceData();

    public function getServiceIdField()
    {
        return "id";
    }

    /**
     * Предварительный фильтр результат запроса
     *
     * @param $data
     * @return array
     */
    public function filterResult($data)
    {
        return $data;
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $cacheId = 'monitor_' . $this->getKey();

        $result = [];

        if (Yii::$app->request->get('page') && Yii::$app->cache->exists($cacheId)) {
            $result = Yii::$app->cache->get($cacheId);

            return new ArrayDataProvider([
                'allModels' => $result,
            ]);
        }

        $platformaResult = ArrayHelper::map(
            $this->getServiceData(),
            "service_id",
            "account_id"
        );

        $statResult = $this->getStatData();

        $platformaResult = $this->filterResult($platformaResult);
        $statResult = $this->filterResult($statResult);

        $platformaKeys = array_keys($platformaResult);
        $statKeys = array_keys($statResult);

        foreach (array_diff($platformaKeys, $statKeys) as $platformaUsageId) {
            $result[$platformaUsageId] = [
                'usage_id' => $platformaUsageId,
                'account_id' => $platformaResult[$platformaUsageId],
                'status' => self::STATUS_IN_PLATFORM
            ];
        }

        foreach (array_diff($statKeys, $platformaKeys) as $statUsageId) {
            $result[$statUsageId] = [
                'usage_id' => $statUsageId,
                'account_id' => $statResult[$statUsageId],
                'status' => self::STATUS_IN_STAT
            ];
        }

        foreach (array_intersect($platformaKeys, $statKeys) as $usageId) {
            if ($platformaResult[$usageId] != $statResult[$usageId]) {
                $result[$usageId] = [
                    'usage_id' => $usageId,
                    'account_id' => $statResult[$usageId],
                    'account_id2' => $platformaResult[$usageId],
                    'status' => self::STATUS_ACCOUNT_DIFF
                ];
            }
        }

        ksort($result);
        Yii::$app->cache->set($cacheId, $result, DependecyHelper::TIMELIFE_HOUR);

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}