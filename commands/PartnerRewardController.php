<?php

namespace app\commands;

use app\classes\partners\RewardCalculate;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\EventQueue;
use app\models\PartnerRewards;
use DateTime;
use yii\console\Controller;

class PartnerRewardController extends Controller
{
    /**
     * Расчет партнерских вознаграждений за последние 24 месяца
     */
    public function actionCalculateRewardsFor24Months()
    {
        echo 'Удаление всех записей, где событие: ' . EventQueue::PARTNER_REWARD . PHP_EOL;
        EventQueue::deleteAll(['event' => EventQueue::PARTNER_REWARD]);

        echo 'Удаление все партнерских вознаграждений перед перерасчетом' . PHP_EOL;
        PartnerRewards::deleteAll();

        // Получение всех счетов за последние 24 месяца
        $date = (new DateTime)->modify('-2 years')->format('Y-m-d');
        echo 'Получение всех счетов, начиная с ' . $date . PHP_EOL;
        $bills = Bill::find()->where(['>', 'bill_date', $date]);
        /**
         * Перерасчет всех партнерских вознаграждений за последние 24 месяца
         * @uses ClassName: PartnerRewardsCalculation, Method: calculateRewards
         */
        $createdAt = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        foreach ($bills->each() as $bill) {
            /** @var Bill $bill */
            try {
                RewardCalculate::run($bill->client_id, $bill->id, $createdAt);
                echo '. ';
            } catch (\yii\base\Exception $e) {
                echo 'Bill#' . $bill->id . ': ' . $e->getMessage() . PHP_EOL;
            }
        }
    }
}