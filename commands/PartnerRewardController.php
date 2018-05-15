<?php

namespace app\commands;

use app\classes\partners\RewardCalculate;
use app\models\Bill;
use app\models\EventQueue;
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

		// Получение всех счетов за последние 24 месяца
		$date = (new DateTime)->modify('-2 years')->format('Y-m-d');
		echo 'Получение всех счетов, начиная с ' . $date . PHP_EOL;
		$bills = Bill::find()->where(['>', 'bill_date', $date]);
		// Добавление выбранных счетов в очередь для перерасчета партнерских вознаграждений
		foreach ($bills->each() as $bill) {
			/** @var Bill $bill */
			try {
				RewardCalculate::run($bill->client_id, $bill->id, $bill->bill_date);
				echo '. ';
			} catch (\yii\base\Exception $e) {
				echo $e->getMessage() . PHP_EOL;
			}
		}
	}
}