<?php
namespace app\commands;

use app\models\voip\StatDay;
use app\models\voip\StatisticDay;
use app\models\voip\StatisticMonth;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use Yii;
use DateTime;
use app\models\ClientAccount;
use yii\base\Exception;
use yii\console\Controller;
use yii\db\Expression;


class StatisticController extends Controller
{
    const PERIOD_YESTERDAY = 'yesterday';
    const PERIOD_MONTH = 'month';
    const PERIOD_PREVMONTH = 'prevmonth';
    const PERIOD_HALFYEAR = 'halfyear';
    /**
     * Обновление агрегированной статистики по звонкам
     *
     * @param string $period
     * @param int $accountId
     */
    public function actionUpdate($period = self::PERIOD_HALFYEAR, $accountId = null)
    {
        if (!in_array($period, [self::PERIOD_YESTERDAY, self::PERIOD_MONTH, self::PERIOD_PREVMONTH, self::PERIOD_HALFYEAR])) {
            throw new \InvalidArgumentException("Неправильный параметр периода: {$period}");
        }

        /** @var DateTime $nowStart */
        $nowStart = (new \DateTimeImmutable('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->setTime(0, 0, 0);

        /** @var DateTime $nowEnd */
        $nowEnd = $nowStart->modify("+1 day");

        switch ($period) {
            case self::PERIOD_YESTERDAY:
                $from = $nowStart->modify("-1 day");
                $to = $nowEnd->modify("-1 day");

                if ($nowStart->format('d') == 1) {
                    $monthSchema = [-1, -1];
                } else {
                    $monthSchema = [0, 0];
                }
                break;

            case self::PERIOD_PREVMONTH:
                $from = $nowStart->modify('first day of previous month');
                $to = $nowEnd->modify('first day of this month');
                $monthSchema = [-1, -1];
                break;

            case self::PERIOD_MONTH:
                $from = $nowStart->modify('first day of this month');
                $to = $nowEnd;
                $monthSchema = [0, 0];
                break;

            case self::PERIOD_HALFYEAR:
                $from = $nowStart->modify('first day of this month')->modify("-6 month");
                $to = $nowEnd;
                $monthSchema = [-6, 0];
                break;

            default:
                throw new \LogicException("Неправильный параметр периода: {$period}");
        }

        $clientAccountQuery = ClientAccount::find()->active();

        $accountId && $clientAccountQuery->andWhere(['id' => $accountId]);

        /** @var ClientAccount $clientAccount */
        foreach ($clientAccountQuery->each() as $clientAccount) {

            echo PHP_EOL . date("r") . " :" . $clientAccount->id;
            /** @var DateTime $clientFrom */
            $clientFrom = $from->setTimezone(new \DateTimeZone($clientAccount->timezone_name));
            /** @var DateTime $clientTo */
            $clientTo = $to->setTimezone(new \DateTimeZone($clientAccount->timezone_name));
            $offset = $clientFrom->getOffset();

            $transaction = Yii::$app->getDb()->beginTransaction();
            try {

                $callsQuery = CallsAggr::find()
                    ->select([
                        'calls_date' => new Expression("DATE_TRUNC('day', aggr_time + '" . $offset . " second'::interval)::date"),
                        'count' => new Expression('SUM(total_calls)'),
                        'cost' => new Expression('SUM(cost)')
                    ])
                    ->where(['AND',
                        ['>=', 'aggr_time', $clientFrom->format(DATE_ATOM)],
                        ['<', 'aggr_time', $clientTo->format(DATE_ATOM)],
                        ['account_id' => $clientAccount->id]
                    ])
                    ->groupBy('calls_date')
                    ->orderBy('calls_date')
                    ->asArray();

                StatisticDay::deleteAll([
                    'AND',
                    ['account_id' => $clientAccount->id],
                    ['>=', 'date', $from->format(DateTimeZoneHelper::DATE_FORMAT)],
                    ['<', 'date', $to->format(DateTimeZoneHelper::DATE_FORMAT)]
                ]);

                echo "...";
                $insertData = [];
                foreach ($callsQuery->each(1000, CallsAggr::getDb()) as $call) {
                    $insertData[] = [$clientAccount->id, $call['calls_date'], $call['count'], $call['cost']];
                }

                if ($insertData) {
                    Yii::$app->db
                        ->createCommand()
                        ->batchInsert(StatisticDay::tableName(), [
                            'account_id',
                            'date',
                            'count',
                            'cost'
                        ], $insertData)
                        ->execute();
                }

                echo count($insertData);

                $this->_recalcMonthStatistic($clientAccount->id, $monthSchema);

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e);
            }
        }
    }

    /**
     * Пересчет агрегированных данных за месяц
     *
     * @param int $accountId
     * @param int[] $monthSchema
     */
    private function _recalcMonthStatistic($accountId, $monthSchema)
    {
        /** @var DateTime $start */
        $start = (new \DateTimeImmutable('now'))->modify('first day of this month')->setTime(0, 0, 0);

        /** @var DateTime $end */
        $end = $start->modify('+1 month');

        $monthSchema[0] != 0 && ($start = $start->modify($monthSchema[0] . ' month'));
        $monthSchema[1] != 0 && ($end = $end->modify($monthSchema[1] . ' month'));

        StatisticMonth::deleteAll([
            'AND',
            ['account_id' => $accountId],
            ['>=', 'date', $start->format(DateTimeZoneHelper::DATE_FORMAT)],
            ['<', 'date', $end->format(DateTimeZoneHelper::DATE_FORMAT)]
        ]);

        $periodStart = $start;

        $periodEnd = $periodStart->modify('+1 month');

        $insertData = [];
        do {
            $statDayQuery = StatisticDay::find()
                ->where([
                    'AND',
                    ['account_id' => $accountId],
                    ['>=', 'date', $periodStart->format(DateTimeZoneHelper::DATETIME_FORMAT)],
                    ['<', 'date', $periodEnd->format(DateTimeZoneHelper::DATETIME_FORMAT)]
                ])
                ->select([
                    'count' => new Expression('SUM(count)'),
                    'cost' => new Expression('SUM(cost)'),
                    'days_with_calls' => new Expression('COUNT(*)')
                ])
                ->asArray();

            foreach ($statDayQuery->each() as $row) {
                if ($row['days_with_calls']) {
                    $insertData[] = [
                        $accountId,
                        $periodStart->format(DateTimeZoneHelper::DATE_FORMAT),
                        (float)$row['count'],
                        (float)$row['cost'],
                        (float)($row['days_with_calls'] ? $row['cost'] / $row['days_with_calls'] : 0),
                        (int)$row['days_with_calls']
                    ];
                }
            }

            $periodStart = $periodStart->modify("+1 month");
            $periodEnd = $periodStart->modify("+1 month");;

        } while ($periodEnd <= $end);

        if ($insertData) {
            Yii::$app->db
                ->createCommand()
                ->batchInsert(
                    StatisticMonth::tableName(),
                    [
                        'account_id',
                        'date',
                        'count',
                        'cost',
                        'average_cost',
                        'days_with_calls'
                    ],
                    $insertData
                )
                ->execute();
        }
    }
}
