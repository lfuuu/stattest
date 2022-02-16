<?php

namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\A2pSms;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Singleton;
use app\models\ClientAccount;

/**
 * @method static A2pSmsDao me($args = null)
 */
class A2pSmsDao extends Singleton
{
    public function getData(ClientAccount $clientAccount,
                            \DateTimeImmutable $firstDayOfDate,
                            \DateTimeImmutable $lastDayOfDate,
                            $offset,
                            $limit,
                            $group_by)
    {
        $tzOffest = $firstDayOfDate->getOffset();

        $firstDayOfDate = $firstDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $lastDayOfDate = $lastDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $query = new Query;
        $query->from(A2pSms::tableName());
        $query->andWhere(['account_id' => $clientAccount->id]);

        if ($offset) {
            $query->offset($offset);
        }

        $query->andWhere(['>=', 'charge_time', $firstDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        $query->andWhere(['<', 'charge_time', $lastDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        
        $limit && $query->limit($limit);
        if (!$group_by || $group_by == 'none') {
            $query->select([
                'id',
                'orig',
                'account_id',
                'charge_time' => ($tzOffest != 0 ? new Expression("charge_time + '" . $tzOffest . " second'::interval") : 'charge_time'),
                'src_number',
                'dst_number',
                'dst_route',
                'cost',
                'rate',
                'count'
            ]);
            $query->orderBy('charge_time');
        }else{
            if ($group_by == 'cost') {
                $groupExp = new Expression('ABS(' . $group_by . ')');
            } else {
                $groupExp = new Expression("DATE_TRUNC('" . $group_by . "', " . ($tzOffest != 0 ? "charge_time + '" . $tzOffest . " second'::interval" : "charge_time") . ")");
            }
            $query->addSelect([
                ($group_by == 'cost' ? 'cost_gr' : 'charge_time') => $groupExp,
                'cost' => new Expression('abs(sum(cost))'),
                'count' => new Expression('count(id)'),
            ]);

            $query->groupBy($groupExp);
            $query->orderBy($groupExp);
        }

        return $query;
    }
}
