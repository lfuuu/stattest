<?php
namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
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

    const CALLS_MAX_LIMIT = 10000;

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
    ) {

        if (CallsAggr::tableName() == $callsTable) {
            $join = "";
            $costPriceField = "cost_price";
        } else {
            $join = "left join " . $callsTable . " as cr2 ON (cr1.peer_id = cr2.id
                            and cr2." . $timeField . " >= :fromDate
                            and cr2." . $timeField . " <= :toDate
            )";
            $costPriceField = "cr2.cost";
        }

        $command =
            CallsRaw::getDb()
                ->createCommand("
                        select
                            case cr1.destination_id <= 0 when true then
                                case cr1.mob when true then 5 else 4 end
                            else cr1.destination_id end rdest,
                            cast( - sum(cr1.cost) as NUMERIC(10,2)) as price,
                            cast(   sum(" . $costPriceField . ") as NUMERIC(10,2)) as cost_price
                        from
                            " . $callsTable . " as cr1
                        " . $join . "
                        where
                            cr1.number_service_id = :numberServiceId
                            and cr1.account_id = :accountId
                            and cr1." . $timeField . " >= :fromDate
                            and cr1." . $timeField . " <= :toDate
                            and abs(cr1.cost) > 0.00001
                        group by rdest
                        having abs(cast( - sum(cr1.cost) as NUMERIC(10,2))) > 0
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
     * @param int $accountId
     * @param string $number
     * @param string $year
     * @param string $month
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getCalls($accountId, $number, $year, $month, $offset = 0, $limit = 1000)
    {
        $firstDayOfDate = new DateTime;
        $firstDayOfDate = $firstDayOfDate->setDate($year, $month, 1);
        $firstDayOfDate = $firstDayOfDate->setTime(0, 0, 0);

        $lastDayOfDate = clone $firstDayOfDate;
        $lastDayOfDate = $lastDayOfDate->modify('last day of this month');
        $lastDayOfDate = $lastDayOfDate->setTime(23, 59, 59);

        $query = new Query;

        $query->select([
            'id',
            'connect_time',
            'src_number',
            'dst_number',
            new Expression("(CASE WHEN orig=false THEN 'in' ELSE 'out' END) AS direction"),
            'billed_time AS length',
            'abs(cost) as cost',
            'rate',
        ]);
        $query->from(CallsRaw::tableName());

        $clientAccount = ClientAccount::findOne($accountId);
        Assert::isObject($clientAccount, 'ClientAccount#' . $accountId);

        $query->andWhere(['account_id' => $clientAccount->id]);

        $regions = ArrayHelper::getColumn(
            UsageVoip::find()
                ->select('region')
                ->client($clientAccount->client)
                ->distinct()
                ->all(),
            'region'
        );
        if (count($regions)) {
            $query->andWhere(['in', 'server_id', $regions]);
        }

        $usage = UsageVoip::find()->where(['E164' => $number])->client($clientAccount->client)->one();
        Assert::isObject($usage, 'Number "' . $number . '"');

        $usages = ArrayHelper::getColumn(
            UsageVoip::find()
                ->select('id')
                ->where(['E164' => $number])
                ->client($clientAccount->client)
                ->distinct()
                ->all(),
            'id'
        );

        if (!count($usages)) {
            return [];
        }

        $query->andWhere(['in', 'number_service_id', $usages]);

        if ($offset) {
            $query->offset($offset);
        }

        $query->andWhere([
            'between',
            'connect_time',
            $firstDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT),
            $lastDayOfDate->format(DateTimeZoneHelper::DATETIME_FORMAT)
        ]);

        $query->limit($limit > self::CALLS_MAX_LIMIT ? self::CALLS_MAX_LIMIT : $limit);
        $query->orderBy('connect_time');

        return $query->all(CallsRaw::getDb());
    }

}
