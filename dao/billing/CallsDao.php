<?php
namespace app\dao\billing;

use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Singleton;
use app\models\UsageVoip;
use app\models\billing\Calls;

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
    public function getCalls($accountId = 0, $number = '', $year = '', $month = '', $offset = 0, $limit = 1000)
    {
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

        if ($accountId) {
            $usage = UsageVoip::find()->client('id' . $accountId)->actual()->one();

            if ($usage instanceof UsageVoip) {
                $query->andWhere(['account_id' => $usage->clientAccount->id]);
            }
        }
        if ($number) {
            $usage = UsageVoip::find()->where(['E164' => $number])->actual()->one();
            if ($usage instanceof UsageVoip) {
                $query->andWhere(['number_service_id' => $usage->id]);
            }
        }
        if ($year) {
            $query->andWhere("date_part('year', connect_time) = :year", [':year' => $year]);
        }
        if ($month) {
            $query->andWhere("date_part('month', connect_time) = :month", [':month' => $month]);
        }
        if ($offset) {
            $query->offset($offset);
        }
        $query->limit($limit > self::CALLS_MAX_LIMIT ? self::CALLS_MAX_LIMIT : $limit);

        return $query->all(Calls::getDb());
    }

}
