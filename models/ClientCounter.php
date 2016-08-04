<?php

namespace app\models;

use app\models\billing\Counter as BillingCounter;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $client_id
 * @property float $amount_sum
 * @property float $amount_day_sum
 * @property float $amount_month_sum
 * @property float $subscription_rt_balance
 * @property float $subscription_rt_last_month
 * @property float $subscription_rt
 * @property float $realtimeBalance
 * @property float $totalSummary
 * @property float $daySummary
 * @property float $monthSummary
 *
 * @property ClientAccount clientAccount
 */
class ClientCounter extends ActiveRecord
{

    // Индефикатор локальности данных
    public $isLocal = false;

    // Локальный кеш
    private static $localCache = [];

    // Локальный кеш, для ускорения массовых запросов
    private static $localCacheFastMass = [];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_counters';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_id']);
    }

    /**
     * Возвращает текущий баланс лицевого счета
     * @return float
     */
    public function getRealtimeBalance()
    {
        switch ($this->clientAccount->account_version) {

            case ClientAccount::VERSION_BILLER_USAGE:
                // старый (текущий) биллинг
                return
                    $this->clientAccount->credit > -1
                        ? $this->clientAccount->balance + $this->amount_sum
                        : $this->clientAccount->balance;

            case ClientAccount::VERSION_BILLER_UNIVERSAL:
                // новый (универсальный) биллинг

                // все платежи
                $paymentSummary = Payment::find()
                    ->select(['total_price' => 'SUM(sum)'])
                    ->where(['client_id' => $this->client_id])
                    ->asArray()
                    ->one();

                // все списания
                // счетов меньше, чем транзакций и проводок - считать быстрее
                $billSummary = \app\classes\uu\model\Bill::find()
                    ->select(['total_price' => 'SUM(price)'])
                    ->where(['client_account_id' => $this->client_id])
                    ->asArray()
                    ->one();

                return $paymentSummary['total_price'] - $billSummary['total_price'];

            default:
                throw new \LogicException('Неизвестная версия биллинга у клиента ' . $this->client_id);
        }
    }

    /**
     * Возвращает общую сумму
     * @return float
     */
    public function getTotalSummary()
    {
        return $this->amount_sum;
    }

    /**
     * Возвращает сумму за разговоры за текущий день
     * @return float
     */
    public function getDaySummary()
    {
        return $this->amount_day_sum;
    }

    /**
     * Возвращает сумму за разговоры за текущий месяц
     * @return float
     */
    public function getMonthSummary()
    {
        return $this->amount_month_sum;
    }

    /**
     * @param int $clientAccountId
     * @return ClientCounter
     */
    public static function getCounters($clientAccountId)
    {
        if (isset(static::$localCache[$clientAccountId])) {
            return static::$localCache[$clientAccountId];
        }

        $localCounter = static::getLocalCounter($clientAccountId);

        try {
            /** @var BillingCounter $billingCounter */
            $billingCounter = BillingCounter::findOne(['client_id' => $clientAccountId]);

            $localCounter->amount_sum = $billingCounter->amount_sum;
            $localCounter->amount_day_sum = $billingCounter->amount_day_sum;
            $localCounter->amount_month_sum = $billingCounter->amount_month_sum;
            $localCounter->save();
        } catch (\Exception $e) {
            $localCounter->isLocal = true;
            Yii::error('Failed to load billing data. ' . self::className() . '.', __METHOD__);
        }

        static::$localCache[$clientAccountId] = $localCounter;

        return static::$localCache[$clientAccountId];
    }

    /**
     * @param int $clientAccountId
     * @return ClientCounter
     */
    public static function getCountersFastMass($clientAccountId)
    {
        if (!static::$localCacheFastMass) {
            static::$localCacheFastMass = BillingCounter::find()->addSelect(['client_id'])->indexBy('client_id')->all();
        }

        if (isset(static::$localCacheFastMass[$clientAccountId])) {
            $billingCounter = static::$localCacheFastMass[$clientAccountId];

            $counter = new self;
            $counter->client_id = $clientAccountId;
            $counter->amount_sum = $billingCounter->amount_sum;
            $counter->amount_day_sum = $billingCounter->amount_day_sum;
            $counter->amount_month_sum = $billingCounter->amount_month_sum;
            //$counter->save();

            return $counter;
        }

        $counter = new self;
        $counter->client_id = $clientAccountId;
        $counter->amount_sum = 0;
        $counter->amount_day_sum = 0;
        $counter->amount_month_sum = 0;

        return $counter;
    }

    /**
     * @param int $clientAccountId
     * @return ClientCounter
     */
    private static function getLocalCounter($clientAccountId)
    {
        $counter = self::findOne($clientAccountId);

        if (is_null($counter)) {
            $counter = new ClientCounter;
            $counter->client_id = $clientAccountId;
            if (!$counter->save(true)) {
                throw new \yii\db\Exception("Can't create local counters for clientAccount #" . $clientAccountId);
            }
        }

        return $counter;
    }

}
