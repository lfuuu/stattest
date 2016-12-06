<?php

namespace app\models;

use ActiveRecord\UndefinedPropertyException;
use app\models\billing\Counter as BillingCounter;
use Yii;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property int $client_id
 * @property float $amount_sum
 * @property float $amount_day_sum
 * @property float $amount_mn_day_sum
 * @property float $amount_month_sum
 * @property float $subscription_rt_balance
 * @property float $subscription_rt_last_month
 * @property float $subscription_rt
 * @property float $realtimeBalance
 * @property float $totalSummary
 * @property float $daySummary
 * @property float $dayMnSummary
 * @property float $monthSummary
 *
 * @property ClientAccount clientAccount
 */
class ClientCounter extends ActiveRecord
{

    // Индефикатор локальности данных
    public $isLocal = false;

    // Ошибка синхронизации балансов
    public $isSyncError = false;

    // Локальный кеш
    private static $localCache = [];

    // Локальный кеш, для ускорения массовых запросов. Содержит счетчики из низкоуровнего биллинга
    private static $localCacheFastMass = [];

    // Локальный кеш, для ускорения массовых запросов. Содержит даты последнего обновления баланса в ЛС.
    private static $localCacheFastMassLastAccountDate = [];

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
                // пересчитывается в RealtimeBalanceTarificator
                return $this->clientAccount->balance + $this->getDaySummary();

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
     * Возвращает сумму за разговоры по МН за текущий день
     * @return float
     */
    public function getDayMnSummary()
    {
        return $this->amount_mn_day_sum;
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

        $lastAccountDate = (new Query())
            ->select('last_account_date')
            ->from(ClientAccount::tableName())
            ->where(['id' => $clientAccountId])
            ->createCommand()
            ->queryScalar();

        try {

            if (!$lastAccountDate) {
                throw new \UnexpectedValueException('ЛС не найден');
            }

            /** @var BillingCounter $billingCounter */
            $billingCounter = BillingCounter::findOne(['client_id' => $clientAccountId]);

            if (!$billingCounter) {
                throw new \UnexpectedValueException('BillingCounter для ЛС #' . $clientAccountId . ' не найден');
            }

            if ($billingCounter->amount_date != $lastAccountDate) {
                $localCounter->isSyncError = true;
                throw new \UnexpectedValueException('Пересчет в биллинге не закончен. Нет актуального баланса. ЛС#' . $clientAccountId);
            }

            $localCounter->amount_sum = $billingCounter->amount_sum;
            $localCounter->amount_day_sum = $billingCounter->amount_day_sum;
            $localCounter->amount_mn_day_sum = $billingCounter->amount_mn_day_sum;
            $localCounter->amount_month_sum = $billingCounter->amount_month_sum;
            $localCounter->save();

        } catch(\UnexpectedValueException $e) {
            $localCounter->isLocal = true;
            Yii::warning($e->getMessage());
        } catch (\Exception $e) {
            $localCounter->isLocal = true;
            Yii::error($e->getMessage());
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

            static::$localCacheFastMass = ArrayHelper::index(
                (new Query())
                    ->from(BillingCounter::tableName())
                    ->indexBy('client_id')
                    ->createCommand(BillingCounter::getDb())
                    ->queryAll(),
                'client_id'
            );

            static::$localCacheFastMassLastAccountDate = ArrayHelper::map(
                (new Query())
                    ->select(['id', 'last_account_date'])
                    ->from(ClientAccount::tableName())
                    ->createCommand()
                    ->queryAll(),
                'id',
                'last_account_date'
            );
        }

        if (
            isset(static::$localCacheFastMass[$clientAccountId]) &&
            isset(static::$localCacheFastMassLastAccountDate[$clientAccountId])
        ) {
            $billingLastBillingDate = static::$localCacheFastMass[$clientAccountId]['amount_date'];
            $accountLastBillingDate = static::$localCacheFastMassLastAccountDate[$clientAccountId];

            if ($billingLastBillingDate != $accountLastBillingDate) {
                $billingCounter = static::getLocalCounter($clientAccountId)->toArray();
                Yii::warning('Баланс не синхронизирован. ЛС: ' . $clientAccountId. ' ( billing ' . $billingLastBillingDate . ' != account ' . $accountLastBillingDate . ')');
            } else {
                $billingCounter = static::$localCacheFastMass[$clientAccountId];
            }

            $counter = new self;
            $counter->client_id = $clientAccountId;
            $counter->amount_sum = $billingCounter['amount_sum'];
            $counter->amount_day_sum = $billingCounter['amount_day_sum'];
            $counter->amount_mn_day_sum = $billingCounter['amount_mn_day_sum'];
            $counter->amount_month_sum = $billingCounter['amount_month_sum'];
            //$counter->save();

            return $counter;
        }

        // default counter value
        $counter = new self;
        $counter->client_id = $clientAccountId;
        $counter->amount_sum = 0;
        $counter->amount_day_sum = 0;
        $counter->amount_mn_day_sum = 0;
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

        if (!$counter) {
            $counter = new ClientCounter;
            $counter->client_id = $clientAccountId;
            $counter->amount_sum = 0;
            $counter->amount_day_sum = 0;
            $counter->amount_mn_day_sum = 0;
            $counter->amount_month_sum = 0;
            $counter->subscription_rt_balance = 0;
            $counter->subscription_rt_last_month = 0;
            $counter->subscription_rt = 0;
            if (!$counter->save(true)) {
                throw new \yii\db\Exception("Can't create local counters for clientAccount #" . $clientAccountId);
            }
        }

        return $counter;
    }

}
