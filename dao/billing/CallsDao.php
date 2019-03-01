<?php

namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use app\modules\uu\models\AccountTariff;
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
     * @param int $accountId
     * @param string $number
     * @param string $year
     * @param string $month
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getCalls($accountId, $number, $year, $month, $day, $offset = 0, $limit = 1000)
    {
        $firstDayOfDate = new DateTime;
        $firstDayOfDate = $firstDayOfDate->setDate($year, $month, $day ?: 1);
        $firstDayOfDate = $firstDayOfDate->setTime(0, 0, 0);

        $lastDayOfDate = clone $firstDayOfDate;
        !$day && $lastDayOfDate = $lastDayOfDate->modify('last day of this month');
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

        $usageIds = UsageVoip::find()
            ->where([
                'E164' => $number
            ])
            ->client($clientAccount->client)
            ->select('id')
            ->column();

        $accountTariffIds = null;
        if (!$usageIds) {
            $accountTariffIds = AccountTariff::find()
                ->where([
                    'voip_number' => $number,
                    'client_account_id' => $clientAccount->id,
                ])
                ->select('id')
                ->column();
        }

        Assert::isTrue($usageIds || $accountTariffIds, 'Number "' . $number . '" not found');

        $query->andWhere(['number_service_id' => $usageIds ?: $accountTariffIds]);

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
