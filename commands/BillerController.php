<?php
namespace app\commands;

use app\classes\api\SberbankApi;
use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\SberbankOrder;
use app\models\Transaction;
use Yii;
use DateTime;
use app\classes\bill\ClientAccountBiller;
use app\models\ClientAccount;
use yii\console\Controller;

class BillerController extends Controller
{

    /**
     * Запуск создания транзакций для счетов
     *
     * @return int
     */
    public function actionTariffication()
    {
        define('MONTHLY_BILLING', 1);

        Yii::info("Запущен тарификатор");

        $partSize = 500;
        $date = new DateTime();
        $date->modify('first day of this month');

        try {
            $count = $partSize;
            $offset = 0;
            while ($count >= $partSize) {
                $clientAccounts = ClientAccount::find()
                    ->andWhere(['NOT IN', 'status', [
                        ClientAccount::STATUS_CLOSED,
                        ClientAccount::STATUS_DENY,
                        ClientAccount::STATUS_TECH_DENY,
                        ClientAccount::STATUS_TRASH,
                        ClientAccount::STATUS_ONCE]])
                    ->limit($partSize)
                    ->offset($offset)
                    ->orderBy('id')
                    ->all();

                foreach ($clientAccounts as $clientAccount) {
                    $offset++;
                    $this->_tarifficateClientAccount($clientAccount, $date, $offset);
                }

                $count = count($clientAccounts);
            }
        } catch (\Exception $e) {
            Yii::error('ОШИБКА ТАРИФИКАТОРА');
            Yii::error($e);
            return Controller::EXIT_CODE_ERROR;
        }

        Yii::info("Тарификатор закончил работу");

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Транзакции для ЛС
     *
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @param int $position
     * @return int
     */
    private function _tarifficateClientAccount(ClientAccount $clientAccount, DateTime $date, $position)
    {
        Yii::info("Тарификатор. $position. Лицевой счет: " . $clientAccount->id);

        try {

            ClientAccountBiller::create($clientAccount, $date, $onlyConnecting = false, $connecting = false,
                $periodical = true, $resource = false)
                ->process();

            $resourceDate = clone $date;
            $resourceDate->modify('-1 day');

            ClientAccountBiller::create($clientAccount, $resourceDate, $onlyConnecting = false, $connecting = false,
                $periodical = false, $resource = true)
                ->process();

        } catch (\Exception $e) {
            Yii::error('ОШИБКА ТАРИФИКАТОРА. Лицевой счет: ' . $clientAccount->id);
            Yii::error($e);
            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Генерация событий предполагаемого отключения ЛС после выставления счета
     *
     * @return int
     */
    public function actionForecastAccountBlock()
    {
        define('MONTHLY_BILLING', 1);

        Yii::info("Запущен прогноз отключения клиента при выставлении счета");

        $partSize = 500;
        $date = new DateTime();
        $date->modify('first day of next month');

        $now = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

        $firstDay = clone $now;
        $firstDay->modify('first day of this month');
        $firstDay->setTime(0, 0, 0);

        $lastDay = clone $firstDay;
        $lastDay->modify('last day of this month');
        $lastDay->setTime(23, 59, 59);

        $diffToNow = $firstDay->diff($now);
        $diffToBlock = $lastDay->diff($now);

        $dayToBlock = $diffToBlock->days;
        $dayToNow = $diffToNow->days;

        // потребленный трафик с начала месяца + (сколько дней осталось * среднесуточное потребление + 10%)
        $forecastCoefficient = 1 / ((1 / ($dayToNow + ($dayToBlock * 1.1))) * $dayToNow);

        $importantEventName = null;

        switch($dayToBlock) {
            case 7:
                $importantEventName = ImportantEventsNames::FORECASTING_7DAY;
                break;

            case 3:
                $importantEventName = ImportantEventsNames::FORECASTING_3DAY;
                break;

            case 0:
                $importantEventName = ImportantEventsNames::FORECASTING_1DAY;
                break;

            default:
                throw new \LogicException('Прогнозирование запускается не в тот день.');
        }

        try {
            $count = $partSize;
            $offset = 0;
            while ($count >= $partSize) {
                $clientAccounts = ClientAccount::find()
                    ->andWhere(['NOT IN', 'status', [
                        ClientAccount::STATUS_CLOSED,
                        ClientAccount::STATUS_DENY,
                        ClientAccount::STATUS_TECH_DENY,
                        ClientAccount::STATUS_TRASH,
                        ClientAccount::STATUS_ONCE]])
                    ->limit($partSize)
                    ->offset($offset)
                    ->orderBy('id')
                    ->all();

                foreach ($clientAccounts as $clientAccount) {
                    $offset++;

                    Yii::info("Прогнозирование. $offset. Лицевой счет: " . $clientAccount->id);
                    $forecastBillSum = $this->_forecastingAccountBill($clientAccount, $date, $forecastCoefficient);

                    if ($forecastBillSum && $clientAccount->credit < -$clientAccount->balance + $forecastBillSum) {
                        echo PHP_EOL . $clientAccount->id . ": " . $forecastBillSum;
                        echo " Balance: {$clientAccount->balance} ({$clientAccount->credit} < " . (-$clientAccount->balance + $forecastBillSum) . ")";

                        ImportantEvents::create($importantEventName,
                            ImportantEventsSources::SOURCE_STAT,
                            [
                                'client_id' => $clientAccount->id,
                                'credit' => $clientAccount->credit,
                                'forecast_bill_sum' => $forecastBillSum
                            ]
                        );
                    }
                }

                $count = count($clientAccounts);
            }
        } catch (\Exception $e) {
            Yii::error('Ошибка прогнозирования');
            Yii::error($e);
            return Controller::EXIT_CODE_ERROR;
        }

        Yii::info("Прогнозирование законилось");

        return Controller::EXIT_CODE_NORMAL;
    }


    /**
     * Прогнозирование суммы счета ЛС
     *
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @param float $forecastCoefficient
     * @return int
     * @internal param float $monthPart
     * @internal param int $position
     */
    private function _forecastingAccountBill(ClientAccount $clientAccount, DateTime $date, $forecastCoefficient)
    {
        $billerSubscription = ClientAccountBiller::create(
            $clientAccount,
            $date,
            $onlyConnecting = false,
            $connecting = false,
            $periodical = true,
            $resource = false)
            ->createTransactions();

        $resourceDate = clone $date;
        $resourceDate->modify('-1 day');

        $billerResource = ClientAccountBiller::create(
            $clientAccount,
            $resourceDate,
            $onlyConnecting = false,
            $connecting = false,
            $periodical = false,
            $resource = true,
            $forecastCoefficient)
            ->createTransactions();

        return round(
            array_reduce(
                array_merge(
                    $billerSubscription->getTransactions(),
                    $billerResource->getTransactions()
                ),
                /** @var Transaction $item */
                function ($sum, $item) {
                    return $sum + $item->sum;
                }
            ),
            2);
    }

    /**
     * Завершение сбербанковских платежей
     */
    public function actionSberbankOrdersFinishing()
    {
        $date = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->modify('-3 days');

        $sberbankApi = new SberbankApi();

        $orderQuery = SberbankOrder::find()
            ->where([
                'status' => SberbankOrder::STATUS_REGISTERED
            ])
            ->andWhere(['>', 'created_at', $date->format(DateTimeZoneHelper::DATETIME_FORMAT)]);

        /** @var SberbankOrder $order */
        foreach ($orderQuery->each() as $order) {
            $info = $sberbankApi->getOrderStatusExtended($order->order_id);

            if ($info['orderStatus'] == SberbankOrder::STATUS_PAYED) {
                $order->makePayment($info);

                ClientAccount::dao()->updateBalance($order->bill->client_id);

                echo PHP_EOL . date("r") . ': ' . $order->bill_no . ' - payed';
            }
        }
    }
}
