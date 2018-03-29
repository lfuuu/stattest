<?php
namespace app\commands\stat;

use app\classes\api\ApiVpbx;
use app\helpers\DateTimeZoneHelper;
use app\models\Virtpbx;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class VatsController extends Controller
{

    private $_startPeriods = [
        'today' => ['now', 'now'],
        'yesterday' => ['-1 day', '-1 day'],
        'month' => ['first day of this month', 'now'],
        'prevmonth' => ['first day of previous month', 'last day of previous month'],
    ];

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if ($this->action->id != 'index') {
            require_once Yii::$app->basePath . '/stat/conf.php';
        }

        return parent::beforeAction($this->action);
    }

    /**
     * Index
     */
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
        $this->stdout('month: ' . $this->ansiFormat('получить и сохранить статистику с первого дня текущего месяца, по сегодня'),
            Console::FG_RED);
        $this->stdout("\n\t");
        $this->stdout('prevmonth: ' . $this->ansiFormat('получить и сохранить статистику с первого дня предыдущего месяца, по сегодня'),
            Console::FG_RED);
        $this->stdout("\n\n\t");
        $this->stdout(DateTimeZoneHelper::DATE_FORMAT . ': ' . $this->ansiFormat('получить и сохранить статистику за указанную дату'),
            Console::FG_YELLOW);
        $this->stdout("\n");
    }

    /**
     * @param string $period
     */
    public function actionGet($period = 'today')
    {
        if (array_key_exists($period, $this->_startPeriods)) {
            list($periodStart, $periodEnd) = $this->_startPeriods[$period];
            $periodStart = (new DateTime($periodStart))->setTime('00', '00', '00');
            $periodEnd = (new DateTime($periodEnd))->setTime('00', '00', '00');
        } else {
            try {
                $periodStart = $periodEnd = (new DateTime($period))->setTime('00', '00', '00');
            } catch (\Exception $e) {
                $this->_throwError($e->getMessage());
                return;
            }
        }

        $this->stdout('Скрипт сбора статистики по ВАТС.', Console::BOLD);
        $this->stdout("\n\n");

        while ($periodStart <= $periodEnd) {
            $this->_setDayStatistic($this->_getDayStatistic($periodStart), $periodStart);
            $periodStart->modify('+1 day');
        }
    }

    /**
     * @param \DateTime $date
     * @return array
     */
    private function _getDayStatistic(DateTime $date)
    {
        try {

            return ApiVpbx::me()->getResourceUsagePerDay($date);

        } catch (\Exception $e) {
            if ($e->getCode() != 540) {
                $this->_throwError($e->getMessage());
            }

            return [];
        }
    }

    /**
     * @param array $list
     * @param DateTime $date
     */
    private function _setDayStatistic($list, $date)
    {
        $day = $date->format(DateTimeZoneHelper::DATE_FORMAT);
        $insert = [];

        foreach ($list as $record) {
            if (!$record['disk_space_bytes'] && !$record['int_number_count']) {
                continue;
            }

            $insert[$record['stat_product_id']] = [
                $day,
                $record['account_id'],
                $record['stat_product_id'],
                ($record['callrecord_size'] ?: 0), // disk_space_bytes
                ($record['int_number_count'] ?: 0),
                ($record['ext_did_count'] ?: 0),
                ($record['call_recording_enabled'] ? 1 : 0),
                ($record['faxes_enabled'] ? 1 : 0),
            ];

            $this->stdout('AccountID: ' . $record['account_id'] . ', VirtPBXID: ' . $record['stat_product_id'] . ', Date: ' . $day . "\n" .
                $this->ansiFormat(' space:' . $record['disk_space_bytes'], Console::FG_GREY) .
                $this->ansiFormat(' ports:' . $record['int_number_count'], Console::FG_GREY) .
                $this->ansiFormat(' ext DID counts:' . $record['ext_did_count'], Console::FG_GREY) .
                $this->ansiFormat(' call_recording_enabled:' . $record['call_recording_enabled'], Console::FG_GREY) .
                $this->ansiFormat(' faxes_enabled:' . $record['faxes_enabled'], Console::FG_GREY) .
                "\n\n",
                Console::FG_YELLOW
            );
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Virtpbx::deleteAll(
                [
                    'and',
                    'date = :date',
                    [
                        'in',
                        'client_id',
                        ArrayHelper::getColumn($insert, function ($row) {
                            return $row[1];
                        })
                    ]
                ],
                [
                    ':date' => $day,
                ]
            );

            if (count($insert)) {
                Yii::$app->db->createCommand()->batchInsert(
                    Virtpbx::tableName(),
                    ['date', 'client_id', 'usage_id', 'use_space', 'numbers', 'ext_did_count', 'call_recording_enabled', 'faxes_enabled'],
                    $insert
                )->execute();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->_throwError($e->getMessage());
        }

        $transaction->commit();
    }

    /**
     * @param string $message
     */
    private function _throwError($message)
    {
        $this->stdout('Скрипт сбора статистики по ВАТС.', Console::BOLD);
        $this->stdout("\n\n");
        $this->stdout('Ошибки: ' . "\n\t" . $message . "\n", Console::FG_RED);
        exit;
    }

}