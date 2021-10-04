<?php

namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use app\models\billing\DataRaw;
use app\models\tariffication\Service;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use DateTime;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\billing\CallsRaw;
use yii\helpers\ArrayHelper;

/**
 * @method static DataRawDao me($args = null)
 */
class DataRawDao extends Singleton
{
    public function getData(ClientAccount $clientAccount,
                            $number,
                            \DateTimeImmutable $firstDayOfDate,
                            \DateTimeImmutable $lastDayOfDate,
                            $group_by)
    {
        $tzOffest = $firstDayOfDate->getOffset();

        $firstDayOfDate = $firstDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $lastDayOfDate = $lastDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $query = new Query;
        $query->from(DataRaw::tableName());

        $query->andWhere(['account_id' => $clientAccount->id]);

        if ($number) {
            $usageIds = UsageVoip::dao()->getUsageIdByNumber($number, $clientAccount);
            Assert::isTrue((bool)$usageIds, 'Number "' . $number . '" not found');
            $query->andWhere(['number_service_id' => $usageIds]);
        }

        $query->andWhere(['>=', 'charge_time', $firstDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        $query->andWhere(['<', 'charge_time', $lastDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);

        if (!$group_by || $group_by == 'none') {
            $query->select([
                'charge_time' => ($tzOffest != 0 ? new Expression("charge_time + '" . $tzOffest . " second'::interval") : 'charge_time'),
                'number' => 'msisdn',
                'rate',
                'cost' => new Expression('abs(cost)'),
                'quantity',
            ]);
            $query->orderBy('charge_time');
        } else {

            if ($group_by == 'number') {
                $query->select([
                    'number' => 'msisdn',
                    'cost' => new Expression('ABS(SUM(cost))'),
                    'quantity' => new Expression('SUM(quantity)'),
                ]);
                $query->groupBy('msisdn');
            } else {
                $exp = new Expression("DATE_TRUNC('" . $group_by . "', " . ($tzOffest != 0 ? "charge_time + '" . $tzOffest . " second'::interval" : "charge_time") . ")");
                $query->addSelect([
                    'charge_time' => $exp,
                    'number' => 'msisdn',
                    'cost' => new Expression('ABS(SUM(cost))'),
                    'quantity' => new Expression('SUM(quantity)'),
                ]);

                $groupExp = clone $exp;
                $groupExp->expression .= ', msisdn';

                $query->groupBy($groupExp);
                $query->orderBy($groupExp);
            }
        }

        return $query;
    }
}
