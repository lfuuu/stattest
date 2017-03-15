<?php
namespace app\dao\billing;

use DateTime;
use app\classes\Singleton;
use app\models\UsageVoip;

/**
 * @method static CallsAggrDao me($args = null)
 */
class CallsAggrDao extends Singleton
{
    /**
     * @param UsageVoip $usage
     * @param DateTime $from
     * @param DateTime $to
     * @return mixed
     */
    public static function calcByDest(UsageVoip $usage, DateTime $from, DateTime $to)
    {
        return CallsDao::_calcByDest($usage, $from, $to, 'calls_aggr.calls_aggr', 'aggr_time');
    }
}
