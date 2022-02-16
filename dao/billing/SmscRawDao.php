<?php

namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\SmscRaw;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Singleton;
use app\models\ClientAccount;

/**
 * @method static SmscRawDao me($args = null)
 */
class SmscRawDao extends Singleton
{
    public function getData(ClientAccount $clientAccount,
                            $number,
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
        $query->from(SmscRaw::tableName());

        $query->andWhere(['account_id' => $clientAccount->id]);

        if ($number) {
            $query->andWhere(['OR', ['src_number' => $number], ['dst_number' => $number]]);
        }
        
        $query->andWhere(['>=', 'setup_time', $firstDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        $query->andWhere(['<', 'setup_time', $lastDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);

        if (!$group_by || $group_by == 'none') {
            $query->select([
                'setup_time' => ($tzOffest != 0 ? new Expression("setup_time + '" . $tzOffest . " second'::interval") : 'setup_time'),
                'src_number',
                'dst_number',
                'rate',
                'cost' => new Expression('abs(cost)'),
                'parts' => 'count',
                'count' => new Expression('1'),
            ]);
            $query->orderBy('setup_time');
        } else {
            if ($group_by == 'cost') {
                $groupExp = new Expression('ABS(' . $group_by . ')');
            } else {
                $groupExp = new Expression("DATE_TRUNC('" . $group_by . "', " . ($tzOffest != 0 ? "setup_time + '" . $tzOffest . " second'::interval" : "setup_time") . ")");
            }
            $query->addSelect([
                ($group_by == 'cost' ? 'cost_gr' : 'setup_time') => $groupExp,
                'cost' => new Expression('ROUND(ABS(SUM(cost))::numeric,2)'),
                'parts' => new Expression('SUM(count)'),
                'count' => new Expression('COUNT(*)'),
            ]);

            $query->groupBy($groupExp);
            $query->orderBy($groupExp);

        }

        return $query;
    }
}
