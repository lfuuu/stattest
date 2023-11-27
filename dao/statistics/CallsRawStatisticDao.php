<?php

namespace app\dao\statistics;

use app\models\Number;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\classes\helpers\DependecyHelper;
use app\classes\HttpClient;
use app\classes\Singleton;
use app\dao\billing\CallsDao;
use app\dao\reports\ReportUsageDao;
use app\models\ClientAccount;
use app\models\billing\CallsRaw;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\uu\models\Tariff;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
/**
 * @method static CallsRawStatisticDao me($args = null)
 */
class CallsRawStatisticDao extends Singleton
{
    const CONNECT_MAIN_AND_FAST = 1;
    const CONNECT_SLOW_AND_BIG = 2;
   
    /**
     * @param ClientAccount $clientAccount
     * @param DynamicModel $number
     * @param \DateTime $firstDayOfDate
     * @param \DateTime $lastDayOfDate
     * @return Query
     * @internal param string $year
     * @internal param string $month
     */

    public function getCalls(ClientAccount $clientAccount, $model, \DateTimeImmutable $firstDayOfDate, \DateTimeImmutable $lastDayOfDate)
    {
        $treshold = ReportUsageDao::_getSeparationDate();

        if ($firstDayOfDate < $treshold && $lastDayOfDate > $treshold) {
            $dataCurrent = $this->getCallData($clientAccount, $model, $treshold, $lastDayOfDate, self::CONNECT_MAIN_AND_FAST);
            $dataArchive = $this->getCallData($clientAccount, $model, $firstDayOfDate, $treshold, self::CONNECT_SLOW_AND_BIG);

            $data['result'] = array_merge($dataArchive['result'], $dataCurrent['result']);

            if ($model->is_with_general_info) {
                $data['generalInfo']['sum'] = $dataArchive['generalInfo']['sum'] + $dataCurrent['generalInfo']['sum'];
                $data['generalInfo']['count'] = $dataArchive['generalInfo']['count'] + $dataCurrent['generalInfo']['count'];
                $data['generalInfo']['offset'] = $dataArchive['generalInfo']['offset'] + $dataCurrent['generalInfo']['offset'];
                $data['generalInfo']['limit'] = $dataArchive['generalInfo']['limit'] + $dataCurrent['generalInfo']['limit'];
                $data['generalInfo']['billed_time'] = $dataArchive['generalInfo']['billed_time'] + $dataCurrent['generalInfo']['billed_time'];
            }
        } else {
            $connector = $firstDayOfDate >= $treshold ? self::CONNECT_MAIN_AND_FAST : self::CONNECT_SLOW_AND_BIG;
            $data = $this->getCallData($clientAccount, $model, $firstDayOfDate, $lastDayOfDate, $connector);
        }

        return $data;
    }

    private function getCallData(ClientAccount $clientAccount, $model, \DateTimeImmutable $firstDayOfDate, \DateTimeImmutable $lastDayOfDate, $connector)
    {
        $db = $connector == self::CONNECT_SLOW_AND_BIG ? Yii::$app->dbPgStatistic : CallsRaw::getDb();

        $query = CallsRaw::dao()->getCalls(
            $clientAccount,
            $model->number,
            $firstDayOfDate,
            $lastDayOfDate
        );

        if ($model->is_with_tariff_info) {
            $query->leftJoin(['l' => AccountTariffLight::tableName()], 'l.id = cr.account_tariff_light_id');
            $query->addSelect(['l.tariff_id']);
        }

        $generalInfo = [];

        if ($model->is_with_general_info) {
            $sumQuery = clone $query;
            $sumQuery->select([
                'sum' => new Expression('SUM(-cost)::decimal(12,2)'),
                'count' => new Expression('COUNT(*)'),
                'billed_time' => new Expression('SUM(billed_time)')
            ]);
            $sumQuery->orderBy(null);

            $generalInfo = $sumQuery->one($db);
            $generalInfo['sum'] = (float)$generalInfo['sum'];
        }

        if ($model->offset) {
            $query->offset($model->offset);
        }

        $limit = $model->limit > CallsDao::CALLS_MAX_LIMIT ? CallsDao::CALLS_MAX_LIMIT : $model->limit;

        $generalInfo['offset'] = $model->offset;
        $generalInfo['limit'] = $limit;

        $query->limit($limit);

        static $cTariff = [];
        
        $result = [];

        foreach ($query->each(100, $db) as $call) {
            $call['cost'] = (double)$call['cost'];
            $call['rate'] = (double)$call['rate'];

            if ($model->is_with_nnp_info) {
                $call['nnp']['src'] = $this->_getNnpInfo($call['src_number']);
                $call['nnp']['dst'] = $this->_getNnpInfo($call['dst_number']);
            }

            if ($model->is_with_tariff_info) {
                $tariffName = null;
                if ($call['tariff_id']) {
                    if (isset($cTariff[$call['tariff_id']])) {
                        $tariffName = $cTariff[$call['tariff_id']];
                    } else {
                        $tariffName = Tariff::find()->where(['id' => $call['tariff_id']])->select(['name'])->scalar();
                        $cTariff[$call['tariff_id']] = $tariffName;
                    }
                }

                $call['tariff_name'] = $tariffName;
                unset($call['tariff_id']);
            }

            $result[] = $call;
        }

        $data['result'] = $result;
        if (isset($generalInfo)) {
            $data['generalInfo'] = $generalInfo;
        }
        
        return $data;
    }

    private function _getNnpInfo($number)
    {
        if (!$number) {
            return null;
        }

        /** @var yii\redis\Cache $redis */
        $cache = \Yii::$app->cache;

        if ($numberInfo = $cache->get('numberInfo:' . $number)) {
            return unserialize($numberInfo);
        }

        $numberInfo = Number::getNnpInfo($number);

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->redis;

        $data = [
            'country_name' => $redis->get('country:' . $numberInfo['country_code']) ?: 'unknown',
            'city_name' => $redis->get('city:' . $numberInfo['nnp_city_id']) ?: 'unknown',
            'region_name' => $redis->get('region:' . $numberInfo['nnp_region_id']) ?: 'unknown',
            'operator_name' => $redis->get('operator:' . $numberInfo['nnp_operator_id']) ?: 'unknown',
            'ndc_type_name' => $redis->get('ndcType:' . $numberInfo['ndc_type_id']) ?: 'unknown',
            'operator_id' => $numberInfo['nnp_operator_id'],
        ];

        /** @var yii\redis\Cache $redis */
        $cache = \Yii::$app->cache;
        $cache->set('numberInfo:' . $number, serialize($data), DependecyHelper::TIMELIFE_HALF_MONTH, (new TagDependency(['tags' => [DependecyHelper::TAG_NUMBER_INFO]])));

        return $data;
    }
}
