<?php

namespace app\models\voip;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use app\models\ClientAccount;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class StatisticDay
 *
 * @property integer $account_id
 * @property string $date
 * @property integer $count
 * @property integer $cost
 */
class StatisticDay extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'stat_voip_day';
    }

    /**
     * Счетчики для дашборда в ЛК
     *
     * @param ClientAccount $account
     * @return array
     */
    public static function getCounters(ClientAccount $account)
    {
        $from = (new \DateTimeImmutable('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->setTime(0, 0, 0);


        $clientThisDayStart = $from->setTimezone(new \DateTimeZone($account->timezone_name));
        $clientThisMonthStart = $from->modify('first day of this month')->setTimezone(new \DateTimeZone($account->timezone_name));

        /** @var DateTime $prevMonthStart */
        $prevMonthStart = (new \DateTimeImmutable('now'))->modify('first day of previous month')->setTime(0, 0, 0);

        /** @var DateTime $thisMonthStart */
        $thisMonthStart = $prevMonthStart->modify('+1 month');

        $avgDayPrevMonth = StatisticMonth::find()
            ->where([
                'account_id' => $account->id,
                'date' => $prevMonthStart->format(DateTimeZoneHelper::DATE_FORMAT)
            ])
            ->select('average_cost')
            ->scalar();

        $avgDayThisMonth = StatisticMonth::find()
            ->where([
                'account_id' => $account->id,
                'date' => $thisMonthStart->format(DateTimeZoneHelper::DATE_FORMAT)
            ])
            ->select('average_cost')
            ->scalar();

        $avgDayAllTime = (float)(new Query())
            ->from(StatisticMonth::tableName())
            ->where(['account_id' => $account->id])
            ->select(['value' => new Expression('if(days_with_calls> 0, sum(cost) / sum(days_with_calls), 0)')])
            ->scalar();

        $thisDay = CallsAggr::find()
            ->where(['account_id' => $account->id])
            ->andWhere(['>=', 'aggr_time', $clientThisDayStart->format(DATE_ATOM)])
            ->sum('cost');

        $thisMonth = CallsAggr::find()
                ->where(['account_id' => $account->id])
                ->andWhere(['>=', 'aggr_time', $clientThisMonthStart->format(DATE_ATOM)])
                ->sum('cost') + $thisDay;

        return [
            'avgDayPrevMonth' => -round($avgDayPrevMonth, 2),
            'avgDayThisMonth' => -round($avgDayThisMonth, 2),
            'avgDayAllTime' => -round($avgDayAllTime, 2),
            'thisDay' => -round($thisDay, 2),
            'thisMonth' => -round($thisMonth, 2),
        ];
    }
}
