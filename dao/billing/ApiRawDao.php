<?php

namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\api\ApiRaw;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Singleton;
use app\models\ClientAccount;

/**
 * @method static ApiRawDao me($args = null)
 */
class ApiRawDao extends Singleton
{
    public function getData(ClientAccount $clientAccount,
                            \DateTimeImmutable $firstDayOfDate,
                            \DateTimeImmutable $lastDayOfDate,
                            $uniqueId,
                            $offset,
                            $limit,
                            $group_by,
                            $is_with_general_info)
    {
        $tzOffest = $firstDayOfDate->getOffset();

        $firstDayOfDate = $firstDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $lastDayOfDate = $lastDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $query = new Query;
        $query->from(ApiRaw::tableName());
        $query->andWhere(['account_id' => $clientAccount->id]);
        
        if ($uniqueId) {
            $query->andWhere(['unique_id' => $uniqueId]);
        }

        if ($offset) {
            $query->offset($offset);
        }

        $query->andWhere(['>=', 'connect_time', $firstDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        $query->andWhere(['<', 'connect_time', $lastDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        
        $generalInfo = [];
        if ($is_with_general_info) {
            $sumQuery = clone $query;
          
            $sumQuery->select([
                'sum' => new Expression('SUM(-cost)::decimal(12,2)'),
                'count' => new Expression('COUNT(*)')
            ]);
            $sumQuery->orderBy(null);

            $generalInfo = $sumQuery->one(ApiRaw::getDb());
            $generalInfo['sum'] = (float)$generalInfo['sum'];
            $generalInfo['offset'] = $offset;
            $generalInfo['limit'] = $limit;            
        }

        $limit && $query->limit($limit);
        if (!$group_by || $group_by == 'none') {
            $query->select([
                'id',
                'connect_time' => ($tzOffest != 0 ? new Expression("connect_time + '" . $tzOffest . " second'::interval") : 'connect_time'),
                'account_id',
                'api_id',
                'api_method_id',
                'service_api_id',
                'api_count',
                'api_weight',
                'unique_id',
                'rate',
                'cost' => new Expression('abs(cost)'),
            ]);
            $query->orderBy('connect_time');
        }else{
            $groupExp = new Expression("DATE_TRUNC('" . $group_by . "', " . ($tzOffest != 0 ? "connect_time + '" . $tzOffest . " second'::interval" : "connect_time") . ")");
            $query->addSelect([
                'connect_time' => $groupExp,
                'cost' => new Expression('abs(sum(cost))'),
                'count' => new Expression('count(id)'),
            ]);

            $query->groupBy($groupExp);
            $query->orderBy($groupExp);
        }

        $result = [];
        foreach ($query->each(500, ApiRaw::getDb()) as $data) {
            $data['cost'] = (double)$data['cost'];

            if (isset($data['rate'])) {
                $data['rate'] = (double)$data['rate'];
            }

            $result[] = $data;
        }
        $result['result'] = $result;
        $result['generalInfo'] = $generalInfo;

        return $result;
    }
}
