<?php
namespace app\dao\billing;

use DateTime;
use app\classes\Singleton;
use app\models\UsageVoip;
use app\dao\billing\CallsDao;

class CallsAggrDao extends Singleton
{
    public function calcByDest(UsageVoip $usage, DateTime $from, DateTime $to)
    {
        return CallsDao::_calcByDest($usage, $from, $to, 'calls_aggr.calls_aggr', 'aggr_time');
    }
}
