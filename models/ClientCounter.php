<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\billing\CachedCounter as BillingCounter;
use app\models\billing\Locks;
use Yii;
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
 * @property float $sum_w_neg_rate
 * @property float $sum_w_neg_rate_day
 * @property float $sum_w_neg_rate_month
 * @property float $voice_sum_day
 * @property float $voice_sum_month
 * @property float $data_sum_day
 * @property float $data_sum_month
 * @property float $sms_sum_day
 * @property float $sms_sum_month
 *
 * @property-read ClientAccount $clientAccount
 */
class ClientCounter extends ActiveRecord
{

    // Индефикатор локальности данных
    public $isLocal = false;

    // Ошибка синхронизации балансов
    public $isSyncError = false;

    // массовый пересчет
    public $isMass = false;

    // Локальный кеш
    private static $_localCache = [];

    // Локальный кеш, для ускорения массовых запросов. Содержит счетчики из низкоуровнего биллинга
    private static $_localCacheFastMass = [];

    // Локальный кеш, для ускорения массовых запросов. Содержит даты последнего обновления баланса в ЛС.
    private static $_localCacheFastMassLastAccountDate = [];

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
        return $this->hasOne(ClientAccount::class, ['id' => 'client_id']);
    }

    /**
     * Возвращает текущий баланс лицевого счета
     *
     * @return float
     */
    public function getRealtimeBalance()
    {
        switch ($this->clientAccount->account_version) {

            case ClientAccount::VERSION_BILLER_USAGE:
                // старый (текущий) биллинг

//                Yii::info('Баланс '.($this->isMass ? 'массовый' : 'нормальный').' ' .($this->isLocal ? 'неверный' : ''). ' ЛС# ' . $this->clientAccount->id . ' '
//                    . ($this->clientAccount->balance + $this->amount_sum) . ' = ' . $this->clientAccount->balance . ' + ' . $this->amount_sum);

                return
                    $this->clientAccount->credit > -1 ?
                        $this->clientAccount->balance + $this->amount_sum :
                        $this->clientAccount->balance;


            case ClientAccount::VERSION_BILLER_UNIVERSAL:
                // новый (универсальный) биллинг
                // пересчитывается в RealtimeBalanceTarificator

//                Yii::info('Баланс ' . ($this->isMass ? 'массовый' : 'нормальный') . ' ' . ($this->isLocal ? 'неверный' : '') . ' УЛС# ' . $this->clientAccount->id . ' '
//                    . ($this->clientAccount->balance + $this->getDaySummary()) . ' = ' . $this->clientAccount->balance . ' + ' . $this->getDaySummary() . ' / ' . $this->amount_sum);

                return $this->clientAccount->balance + $this->amount_sum;

            default:
                throw new \LogicException('Неизвестная версия биллинга у клиента ' . $this->client_id);
        }
    }

    /**
     * Возвращает общую сумму
     *
     * @return float
     */
    public function getTotalSummary()
    {
        return $this->amount_sum;
    }

    /**
     * Возвращает сумму за разговоры за текущий день
     *
     * @return float
     */
    public function getDaySummary()
    {
        return $this->amount_day_sum;
    }

    /**
     * Возвращает сумму за разговоры по МН за текущий день
     *
     * @return float
     */
    public function getDayMnSummary()
    {
        return $this->amount_mn_day_sum;
    }

    /**
     * Возвращает сумму за разговоры за текущий месяц
     *
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
        if (isset(static::$_localCache[$clientAccountId])) {
            return static::$_localCache[$clientAccountId];
        }

        $localCounter = static::_getLocalCounter($clientAccountId);

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

            BillingCounter::setPgTimeout(Locks::PG_ACCOUNT_TIMEOUT);

            /** @var BillingCounter $billingCounter */
            $billingCounter = BillingCounter::findOne(['client_id' => $clientAccountId]);

            if (!$billingCounter) {
                throw new \UnexpectedValueException('BillingCounter для ЛС #' . $clientAccountId . ' не найден');
            }

            if ($billingCounter->amount_date != $lastAccountDate) {
                $localCounter->isSyncError = true;
                throw new \UnexpectedValueException('Баланс неверный.  ЛС#' . $clientAccountId . ' ' . $billingCounter->amount_date . ' != ' . $lastAccountDate . '(' . $billingCounter->amount_sum . ', ' . $localCounter->amount_sum . ')');
            }

            $localCounter->amount_sum = $billingCounter->amount_sum;
            $localCounter->amount_day_sum = $billingCounter->amount_day_sum;
            $localCounter->amount_mn_day_sum = $billingCounter->amount_mn_day_sum;
            $localCounter->amount_month_sum = $billingCounter->amount_month_sum;

            $localCounter->sum_w_neg_rate = $billingCounter->sum_w_neg_rate;
            $localCounter->sum_w_neg_rate_day = $billingCounter->sum_w_neg_rate_day;
            $localCounter->sum_w_neg_rate_month = $billingCounter->sum_w_neg_rate_month;

            $localCounter->voice_sum_day = $billingCounter->voice_sum_day;
            $localCounter->voice_sum_month = $billingCounter->voice_sum_month;

            $localCounter->data_sum_day = $billingCounter->data_sum_day;
            $localCounter->data_sum_month = $billingCounter->data_sum_month;

            $localCounter->sms_sum_day = $billingCounter->sms_sum_day;
            $localCounter->sms_sum_month = $billingCounter->sms_sum_month;

            $localCounter->save();

        } catch (\UnexpectedValueException $e) {
            $localCounter->isLocal = true;
            Yii::warning($e->getMessage());
        } catch (\Exception $e) {
            $localCounter->isLocal = true;
            Yii::error($e->getMessage());
        }

        static::$_localCache[$clientAccountId] = $localCounter;

        return static::$_localCache[$clientAccountId];
    }

    /**
     * @param int $clientAccountId
     * @return ClientCounter
     */
    public static function getCountersFastMass($clientAccountId)
    {
        if (!static::$_localCacheFastMass) {

            static::$_localCacheFastMass = ArrayHelper::index(
                (new Query())
                    ->from(BillingCounter::tableName())
                    ->indexBy('client_id')
                    ->createCommand(BillingCounter::getDb())
                    ->queryAll(),
                'client_id'
            );

            static::$_localCacheFastMassLastAccountDate = ClientAccount::getListTrait(
                $isWithEmpty = false,
                $isWithNullAndNotNull = false,
                $indexBy = 'id',
                $select = 'last_account_date',
                $orderBy = [],
                $where = []);
        }

        if (
        isset(
            static::$_localCacheFastMass[$clientAccountId],
            static::$_localCacheFastMassLastAccountDate[$clientAccountId]
        )
        ) {
            $billingLastBillingDate = static::$_localCacheFastMass[$clientAccountId]['amount_date'];
            $accountLastBillingDate = static::$_localCacheFastMassLastAccountDate[$clientAccountId];

            $isLocal = false;

            if ($billingLastBillingDate != $accountLastBillingDate) {
                $billingCounter = static::_getLocalCounter($clientAccountId)->toArray();
                Yii::warning('Баланс массовый не синхронизирован. ЛС#' . $clientAccountId . ' (' . $billingLastBillingDate . ' != ' . $accountLastBillingDate . ') ' . static::$_localCacheFastMass[$clientAccountId]['amount_sum'] . ', ' . $billingCounter['amount_sum']);
                $isLocal = true;
            } else {
                $billingCounter = static::$_localCacheFastMass[$clientAccountId];
            }

            $counter = new self;
            $counter->client_id = $clientAccountId;
            $counter->amount_sum = $billingCounter['amount_sum'];
            $counter->amount_day_sum = $billingCounter['amount_day_sum'];
            $counter->amount_mn_day_sum = $billingCounter['amount_mn_day_sum'];
            $counter->amount_month_sum = $billingCounter['amount_month_sum'];
            $counter->isMass = true;
            $counter->isLocal = $isLocal;
            // $counter->save();
            return $counter;
        }

        // default counter value
        $counter = new self;
        $counter->client_id = $clientAccountId;
        $counter->amount_sum = 0;
        $counter->amount_day_sum = 0;
        $counter->amount_mn_day_sum = 0;
        $counter->amount_month_sum = 0;
        $counter->isMass = true;

        return $counter;
    }

    /**
     * @param int $clientAccountId
     * @return ClientCounter
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    private static function _getLocalCounter($clientAccountId)
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
                throw new ModelValidationException($counter);
            }
        }

        return $counter;
    }

}
