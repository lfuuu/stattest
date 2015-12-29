<?php
namespace app\dao\billing;

use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\billing\Calls;
use yii\helpers\ArrayHelper;

class CallsDao extends Singleton
{

    const CALLS_MAX_LIMIT = 10000;

    public function calcByDest(UsageVoip $usage, DateTime $from, DateTime $to)
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
            \Yii::$app->get('dbPg')
                ->createCommand("
                        select
                            case destination_id <= 0 when true then
                                case mob when true then 5 else 4 end
                            else destination_id end rdest,
                            cast( - sum(cost) as NUMERIC(10,2)) as price
                        from
                            ".$callsTable."
                        where
                            number_service_id = '" . $usage->id . "'
                            and ".$timeField." >= '" . $from->format('Y-m-d H:i:s') . "'
                            and ".$timeField." <= '" . $to->format('Y-m-d H:i:s') . "'
                            and abs(cost) > 0.00001
                        group by rdest
                        having abs(cast( - sum(cost) as NUMERIC(10,2))) > 0
                    "
                );

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
            'cost',
            'rate',
        ]);
        $query->from(Calls::tableName());

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

        $usage = UsageVoip::find()->where(['E164' => $number])->one();
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

        $query->andWhere(['between', 'connect_time', $firstDayOfDate->format('Y-m-d H:i:s'), $lastDayOfDate->format('Y-m-d H:i:s')]);

        $query->limit($limit > self::CALLS_MAX_LIMIT ? self::CALLS_MAX_LIMIT : $limit);

        return $query->all(Calls::getDb());
    }

}