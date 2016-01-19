<?php
namespace app\commands\stat;

use Yii;
use DateTime;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use app\models\ActualVirtpbx;
use app\models\Virtpbx;

class VatsController extends Controller
{

    private
        $actualVirtpbx = [],
        $startPeriods = [
            'today' => ['now', 'now'],
            'yesterday' => ['-1 day', '-1 day'],
            'month' => ['first day of this month', 'now'],
            'prevmonth' => ['first day of previous month', 'last day of previous month'],
        ];

    public function beforeAction($action)
    {
        if ($this->action->id != 'index') {
            require_once Yii::$app->basePath . '/stat/conf.php';

            $this->actualVirtpbx = ArrayHelper::map(ActualVirtpbx::find()->select(['client_id', 'usage_id'])->all(), 'usage_id', 'client_id');
        }

        return parent::beforeAction($this->action);
    }

    public function actionIndex()
    {
        $scriptName = basename(Yii::$app->request->scriptFile);

        $this->stdout('Скрипт сбора статистики по ВАТС.', Console::BOLD);
        $this->stdout("\n\n");
        $this->stdout('Параметры запуска:', Console::FG_CYAN);
        $this->stdout("\n\t");
        $this->stdout('./' . $scriptName . ' stat/vats/get ' . $this->ansiFormat('<period>', Console::FG_YELLOW));
        $this->stdout("\n");
        $this->stdout('Варианты периодов:', Console::FG_CYAN);
        $this->stdout("\n\t");
        $this->stdout('today: ' . $this->ansiFormat('получить и сохранить статистику за сегодня'), Console::FG_RED);
        $this->stdout("\n\t");
        $this->stdout('yesterday: ' . $this->ansiFormat('получить и сохранить статистику за вчера'), Console::FG_RED);
        $this->stdout("\n\t");
        $this->stdout('month: ' . $this->ansiFormat('получить и сохранить статистику с первого дня текущего месяца, по сегодня'), Console::FG_RED);
        $this->stdout("\n\t");
        $this->stdout('prevmonth: ' . $this->ansiFormat('получить и сохранить статистику с первого дня предыдущего месяца, по сегодня'), Console::FG_RED);
        $this->stdout("\n\n\t");
        $this->stdout('Y-m-d: ' . $this->ansiFormat('получить и сохранить статистику за указанную дату'), Console::FG_YELLOW);
        $this->stdout("\n");
    }

    public function actionGet($period = 'today')
    {
        if (array_key_exists($period, $this->startPeriods)) {
            list($periodStart, $periodEnd) = $this->startPeriods[$period];
            $periodStart = (new DateTime($periodStart))->setTime('00', '00', '00');
            $periodEnd = (new DateTime($periodEnd))->setTime('00', '00', '00');
        }
        else {
            try {
                $periodStart = $periodEnd = (new DateTime($period))->setTime('00', '00', '00');
            }
            catch (\Exception $e) {
                $this->throwError($e->getMessage());
            }
        }

        $this->stdout('Скрипт сбора статистики по ВАТС.', Console::BOLD);
        $this->stdout("\n\n");

        $day = clone $periodStart;
        while ($day <= $periodEnd) {
            $this->setDayStatistic($this->getDayStatistic($day), $day);
            $day->modify('+1 day');
        }
    }

    /**
     * @param \DateTime $date
     * @return null
     */
    private function getDayStatistic(DateTime $date)
    {
        $result = null;

        try {
            $result = \app\classes\api\ApiVpbx::getResourceStatistics($date);
        }
        catch(\Exception $e) {
            if ($e->getCode() != 540) {
                $this->throwError($e->getMessage());
            }
        }

        return $this->filterDayStatistic($result);
    }

    /**
     * @param array $list
     * @return array
     */
    private function filterDayStatistic($list)
    {
        foreach ($list as &$record) {
            if (!array_key_exists($record['vpbx_id'], $this->actualVirtpbx)) {
                unset($record);
                continue;
            }
        }

        return $list;
    }

    /**
     * @param array $list
     * @param DateTime $date
     */
    private function setDayStatistic($list, $date)
    {
        $day = $date->format('Y-m-d');
        $insert = [];

        foreach ($list as $record) {
            if (!$record['disk_space_bytes'] && !$record['int_number_count']) {
                continue;
            }

            $insert[] = [
                $day,
                $record['account_id'],
                $record['stat_product_id'],
                ($record['disk_space_bytes'] ?: 0),
                ($record['int_number_count'] ?: 0),
                ($record['ext_did_count'] ?: 0),
            ];

            $this->stdout(
                $day . ':' .
                $this->ansiFormat(' space:' . $record['disk_space_bytes'], Console::FG_GREY) .
                $this->ansiFormat(' ports:' . $record['int_number_count'], Console::FG_GREY) .
                $this->ansiFormat(' ext DID counts:' . $record['ext_did_count'], Console::FG_GREY) .
                "\n",
                Console::FG_YELLOW
            );
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Virtpbx::deleteAll(
                [
                    'and',
                    'date = :date',
                    ['in', 'client_id', ArrayHelper::getColumn($insert, function ($row) { return $row[1]; })]
                ],
                [
                    ':date' => $day,
                ]
            );

            Yii::$app->db->createCommand()->batchInsert(
                Virtpbx::tableName(),
                ['date', 'client_id', 'usage_id', 'use_space', 'numbers', 'ext_did_count'],
                $insert
            )->execute();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            $this->throwError($e->getMessage());
        }
    }

    /**
     * @param string $message
     */
    private function throwError($message)
    {
        $this->stdout('Скрипт сбора статистики по ВАТС.', Console::BOLD);
        $this->stdout("\n\n");
        $this->stdout('Ошибки: ' . "\n\t" . $message . "\n", Console::FG_RED);
        exit;
    }

}