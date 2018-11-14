<?php

namespace app\commands;

use app\classes\partners\RewardCalculate;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Currency;
use app\models\EventQueue;
use app\models\PartnerRewards;
use app\models\PartnerRewardsPermanent;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use DateTime;
use yii\console\Controller;
use yii\db\Expression;

class PartnerRewardController extends Controller
{
    /**
     * Перманентное перекачивание данных за прошедшие сутки
     *
     * @param string $type Если значение - full, то выполнить полноценную синхронизацию, предварительно удалив старые записи из таблицы partner_rewards_permanent
     */
    public function actionCreatePermanent($type = '')
    {
        try {
            $db = PartnerRewards::getDb();
            // Если режим работы - полный, то очищаем все данные, находящиеся в PartnerRewardsPermanent
            if ($type === 'full') {
                PartnerRewardsPermanent::deleteAll();
            }
            // Перманентное перекачивание данных с учетом режима работы
            $sql = '
                INSERT INTO
                  ' . PartnerRewardsPermanent::tableName() . ' 
                  (bill_id, line_pk, created_at, once, percentage_once, percentage_of_fee, percentage_of_over, percentage_of_margin, partner_id)
                  (
                    SELECT
                      rewards.bill_id,
                      rewards.line_pk,
                      rewards.created_at,
                      rewards.once,
                      rewards.percentage_once,
                      rewards.percentage_of_fee,
                      rewards.percentage_of_over,
                      rewards.percentage_of_margin,
                      COALESCE(contract.partner_contract_id, contragent.partner_contract_id) partner_id
                    FROM ' . PartnerRewards::tableName() . ' rewards
                      LEFT JOIN ' . Bill::tableName() . ' bill ON rewards.bill_id = bill.id
                      LEFT JOIN ' . ClientAccount::tableName() . ' client ON client.id = bill.client_id
                      LEFT JOIN ' . ClientContract::tableName() . ' contract ON client.contract_id = contract.id
                      LEFT JOIN ' . ClientContragent::tableName() . ' contragent ON contract.contragent_id = contragent.id
                      ' . ($type === 'full' ? '' : 'WHERE rewards.created_at BETWEEN DATE_FORMAT(now() - INTERVAL 1 DAY,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(now() - INTERVAL 1 DAY,\'%Y-%m-%d 23:59:59\')') . '
                  )
                ;
            ';
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

    /**
     * Перенос вознаграждений в счета
     */
    public function actionRewardsToBill()
    {
        $dateFrom = (new \DateTimeImmutable('now'))->setTime(0, 0, 0)->modify('first day of previous month');
        $dateTo = $dateFrom->modify('last day of this month');

        $exp = new Expression('round(coalesce(sum(once), 0), 2) +
            round(coalesce(sum(percentage_once), 0),2) +
            round(coalesce(sum(percentage_of_fee), 0), 2) +
            round(coalesce(sum(percentage_of_over), 0), 2) +
            round(coalesce(sum(percentage_of_margin), 0), 2)');

        $data = PartnerRewardsPermanent::find()
            ->alias('p')
            ->joinWith(['bill b'], true, 'INNER JOIN')
            ->select(['sum' => $exp])
            ->where(['between', 'b.bill_date', $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT), $dateTo->format(DateTimeZoneHelper::DATE_FORMAT)])
            ->groupBy('partner_id')
            ->indexBy('partner_id')
            ->column();


        foreach ($data as $contractId => $sum) {
            $sum = -$sum;
            
            $contract = ClientContract::findOne(['id' => $contractId]);

            if (!$contract || !$contract->isPartner()) {
                continue;
            }

            foreach ($contract->accounts as $account) {

                $lang = $account->contragent->lang_code;

                $bill = Bill::dao()->createBill($account);
                $bill->addLine(
                    \Yii::t(
                        'biller',
                        'partner_reward', [
                        'date_range_month' => \Yii::t(
                            'biller',
                            'date_range_month',
                            [$dateFrom->getTimestamp(), $dateTo->getTimestamp()],
                            $lang)],
                        $lang),
                1,
                $sum);
                Bill::dao()->recalcBill($bill);
                ClientAccount::dao()->updateBalance($bill->client_id);

                echo PHP_EOL . date("r") . ": " . $account->id . ': ' . $sum;

                break;
            }
        }
    }
}
