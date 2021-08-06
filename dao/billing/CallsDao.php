<?php

namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use app\models\tariffication\Service;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\billing\CallsRaw;
use yii\helpers\ArrayHelper;

/**
 * @method static CallsDao me($args = null)
 */
class CallsDao extends Singleton
{

    const CALLS_MAX_LIMIT = 1000000;

    public static function calcByDest(UsageVoip $usage, DateTime $from, DateTime $to)
    {
        if (defined('MONTHLY_BILLING')) {
            return CallsAggrDao::calcByDest($usage, $from, $to);
        } else {
            return self::_calcByDest($usage, $from, $to);
        }
    }

    public static function _calcByDest(
        UsageVoip $usage,
        DateTime $from,
        DateTime $to,
        $callsTable = 'calls_raw.calls_raw',
        $timeField = 'connect_time'
    )
    {

        $command =
            CallsRaw::getDb()
                ->createCommand("
                        SELECT
                            CASE cr1.destination_id <= 0 WHEN TRUE THEN
                                CASE cr1.mob WHEN TRUE THEN 5 ELSE 4 END
                            ELSE cr1.destination_id END rdest,
                            cast( - sum(cr1.cost) AS NUMERIC(10,2)) AS price,
                            0 AS cost_price
                        FROM
                            " . $callsTable . " AS cr1
                        WHERE
                            cr1.number_service_id = :numberServiceId
                            AND cr1.account_id = :accountId
                            AND cr1." . $timeField . " >= :fromDate
                            AND cr1." . $timeField . " <= :toDate
                            AND abs(cr1.cost) > 0.00001
                        GROUP BY rdest
                        HAVING abs(cast( - sum(cr1.cost) AS NUMERIC(10,2))) > 0
                    "
                    , [
                        ':numberServiceId' => $usage->id,
                        ':accountId' => $usage->clientAccount->id,
                        ':fromDate' => $from->format(DateTimeZoneHelper::DATETIME_FORMAT),
                        ':toDate' => $to->format(DateTimeZoneHelper::DATETIME_FORMAT)
                    ]);

        return $command->queryAll();
    }

    /**
     * @param ClientAccount $clientAccount
     * @param string $number
     * @param \DateTimeImmutable $firstDayOfDate
     * @param \DateTimeImmutable $lastDayOfDate
     * @param int $offset
     * @param int $limit
     * @return Query
     * @internal param string $year
     * @internal param string $month
     */
    public function getCalls(ClientAccount $clientAccount, $number, \DateTimeImmutable $firstDayOfDate, \DateTimeImmutable $lastDayOfDate, $offset = 0, $limit = 1000)
    {
        $tzOffest = $firstDayOfDate->getOffset();

        $firstDayOfDate = $firstDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $lastDayOfDate = $lastDayOfDate->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $query = new Query;

        $query->select([
            'cr.id',
            'connect_time' => ($tzOffest != 0 ? new Expression("connect_time + '" . $tzOffest . " second'::interval") : 'connect_time'),
            'src_number',
            'dst_number',
            new Expression("(CASE WHEN orig=false THEN 'in' ELSE 'out' END) AS direction"),
            'billed_time AS length',
            'cost' => new Expression('-cost'),
            'rate',
            'leg_type',
            'location_id',
            'mcn_callid',
            'numA',
            'numB',
            'numC',
        ]);
        $query->from(['cr' => CallsRaw::tableName()]);

        $query->andWhere(['cr.account_id' => $clientAccount->id]);

        $usageIds = UsageVoip::dao()->getUsageIdByNumber($number, $clientAccount);

//        Assert::isTrue((bool)$usageIds, 'Number "' . $number . '" not found');
        $usageIds && $query->andWhere(['number_service_id' => $usageIds]);

        $query->andWhere(['>=', 'connect_time', $firstDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        $query->andWhere(['<', 'connect_time', $lastDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)]);

        $query->orderBy('connect_time');

        return $query;
    }

}
