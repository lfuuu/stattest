<?php
namespace app\dao\billing;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use DateTime;
use app\classes\Singleton;
use app\models\UsageVoip;
use yii\db\Expression;

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

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return $this
     */
    public function getCallCostByPeriod(DateTime $from, DateTime $to)
    {
        return CallsAggr::find()
            ->select([
                'sum_cost' => new Expression('cast( sum(cost) as NUMERIC(10,2) )'),
                'account_id' => 'account_id',
            ])
            ->where(['IS NOT', 'account_id', null])
            ->andWhere(['between', 'aggr_time', $from->format(DateTimeZoneHelper::DATETIME_FORMAT), $to->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->groupBy('account_id')
            ->indexBy('account_id')
            ->column();
    }
}
