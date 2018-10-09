<?php

namespace app\commands;

use app\classes\partners\RewardCalculate;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\EventQueue;
use app\models\PartnerRewards;
use app\models\PartnerRewardsPermanent;
use DateTime;
use yii\console\Controller;

class PartnerRewardController extends Controller
{
    /**
     * Перманентное перекачивание данных за прошедшие сутки
     */
    public function actionCreatePermanent()
    {
        try {
            $db = PartnerRewards::getDb();
            $partnerRewardsPermanentTableName = PartnerRewardsPermanent::tableName();
            $partnerRewardsTableName = PartnerRewards::tableName();
            $columns = implode(',', ['bill_id', 'line_pk', 'created_at', 'once', 'percentage_once', 'percentage_of_fee', 'percentage_of_over', 'percentage_of_margin']);
            // Перманентное перекачивание данных
            $sql = "INSERT INTO {$partnerRewardsPermanentTableName} ({$columns}) (SELECT {$columns} FROM {$partnerRewardsTableName} pr WHERE pr.created_at BETWEEN DATE_FORMAT(now() - INTERVAL 1 DAY,'%Y-%m-%d 00:00:00') AND DATE_FORMAT(now() - INTERVAL 1 DAY,'%Y-%m-%d 23:59:59'));";
            if (!$db->createCommand($sql)->execute()) {
                \Yii::error(sprintf('Ошибка перманентного перекачивания данных: %s', date(DateTimeZoneHelper::DATETIME_FORMAT)));
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * Расчет партнерских вознаграждений по оплаченным счетам за последние сутки
     */
    public function actionCalculateRewardsFor1Day()
    {
        // Получение всех счетов за последние сутки
        $date = (new DateTime)
            ->modify('-1 day')
            ->format(DateTimeZoneHelper::DATE_FORMAT);
        echo 'Получение всех оплаченных счетов, начиная с ' . $date . PHP_EOL;
        $bills = Bill::find()
            ->where(['payment_date' => $date]);
        $this->_calculateRewards($bills);
    }

    /**
     * Расчет партнерских вознаграждений по оплаченным счетам за последние 6 месяцев
     */
    public function actionCalculateRewardsFor6Months()
    {
        echo 'Удаление всех записей, где событие: ' . EventQueue::PARTNER_REWARD . PHP_EOL;
        EventQueue::deleteAll(['event' => EventQueue::PARTNER_REWARD]);
        echo 'Удаление все партнерских вознаграждений перед перерасчетом' . PHP_EOL;
        PartnerRewards::deleteAll();

        // Поиск по оплаченным счетам за последние 6 месяцев
        $date = (new DateTime)
            ->modify('-6 months')
            ->format(DateTimeZoneHelper::DATE_FORMAT);
        echo 'Получение всех оплаченных счетов, начиная с ' . $date . PHP_EOL;
        $bills = Bill::find()
            ->where(['AND',
                ['>=', 'payment_date', $date],
                ['=', 'is_payed', Bill::STATUS_IS_PAID],
            ]);
        $this->_calculateRewards($bills);
    }

    /**
     * @param \app\queries\BillQuery $bills
     */
    private function _calculateRewards($bills)
    {
        /**
         * Перерасчет всех партнерских вознаграждений за последние 24 месяца
         * @uses ClassName: PartnerRewardsCalculation, Method: calculateRewards
         */
        $createdAt = (new \DateTime())->format(DateTimeZoneHelper::DATE_FORMAT);
        foreach ($bills->each() as $bill) {
            /** @var Bill $bill */
            try {
                RewardCalculate::run($bill->clientAccount, $bill, $createdAt);
            } catch (\Exception $e) {
                echo sprintf("Bill %s: %s ", $bill->id, $e->getMessage());
            }
        }
    }
}