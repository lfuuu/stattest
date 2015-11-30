<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Calls;
use DateTime;
use app\models\UsageVoip;

class CallsDao extends Singleton
{
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

}
