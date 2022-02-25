<?php

namespace app\models;

use app\classes\helpers\DependecyHelper;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\billing\CachedCounter as BillingCounter;
use app\models\billing\Locks;
use welltime\graylog\GelfMessage;
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
        Yii::info(
            GelfMessage::create()
                ->setTimestamp(YII_BEGIN_TIME)
                ->setShortMessage('Баланс ' . ($this->isMass ? 'массовый' : 'нормальный') . ' ' . ($this->isLocal ? 'неверный' : '') . ' ЛС# ' . $this->clientAccount->id . ' '
                    . ($this->clientAccount->balance + $this->amount_sum) . ' = ' . $this->clientAccount->balance . ' + ' . $this->amount_sum)
                ->setFullMessage('Баланс ' . ($this->isMass ? 'массовый' : 'нормальный') . ' ' . ($this->isLocal ? 'неверный' : '') . ' ЛС# ' . $this->clientAccount->id . ' '
                    . ($this->clientAccount->balance + $this->amount_sum) . ' = ' . $this->clientAccount->balance . ' + ' . $this->amount_sum)
                ->setAdditional('account_id', $this->clientAccount->id)
                ->setAdditional('balance', $this->clientAccount->balance + $this->amount_sum)
                ->setAdditional('balance_stat', $this->clientAccount->balance)
                ->setAdditional('amount_sum', $this->amount_sum)
                ->setAdditional('balance_stat_date', $this->clientAccount->last_account_date)
                ->setAdditional('is_mass', (int)(bool)$this->isMass)
                ->setAdditional('is_local', (int)(bool)$this->isLocal),
            'balance'
        );

        $rtBalance = $this->clientAccount->credit > -1 ?
            $this->clientAccount->balance + $this->amount_sum :
            $this->clientAccount->balance;

        $cKey = 'rtb' . $this->client_id;
        $cache = \Yii::$app->cache;

        $rtBalanceCached = null;
        if ($cache->exists($cKey)) {
            $rtBalanceCached = $cache->get($cKey);
        }

        // сохраняем последний правильный баланс
        if ($this->isLocal) {
            if ($rtBalanceCached !== null) {
                $rtBalance = $rtBalanceCached;
            }
        } else {
            if ($rtBalanceCached === null || $rtBalanceCached != $rtBalance) {
                $cache->set($cKey, $rtBalance, DependecyHelper::DEFAULT_TIMELIFE);
            }
        }

        Yii::info('Баланс ' . ($this->isMass ? 'массовый' : 'нормальный') . ' ' . ($this->isLocal ? 'неверный (выдаем: ' . $rtBalance . ')' : '') . ' ЛС# ' . $this->clientAccount->id . ' '
            . ($this->clientAccount->balance + $this->amount_sum) . ' = ' . $this->clientAccount->balance . ' + ' . $this->amount_sum);


        switch ($this->clientAccount->account_version) {

            case ClientAccount::VERSION_BILLER_USAGE:
                // старый (текущий) биллинг

                return $rtBalance;

            case ClientAccount::VERSION_BILLER_UNIVERSAL:
                // новый (универсальный) биллинг
                // пересчитывается в RealtimeBalanceTarificator

                return $rtBalance;

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

            $billingCounterCache = \Yii::$app->cache->get('bl' . $clientAccountId);

            if (!$billingCounterCache) {
                BillingCounter::setPgTimeout(Locks::PG_ACCOUNT_TIMEOUT);
                /** @var BillingCounter $billingCounter */
                $billingCounter = BillingCounter::findOne(['client_id' => $clientAccountId]);
            } else {
                /** @var BillingCounter $billingCounter */
                $billingCounter = new BillingCounter();
                $billingCounter->setAttributes($billingCounterCache, false);
            }

            if (!$billingCounter) {
                throw new \UnexpectedValueException('BillingCounter для ЛС #' . $clientAccountId . ' не найден');
            }

            if ($billingCounter->amount_date != $lastAccountDate) {
                $localCounter->isSyncError = true;

                $accountBalance = (new Query())
                    ->select('balance')
                    ->from(ClientAccount::tableName())
                    ->where(['id' => $clientAccountId])
                    ->createCommand()
                    ->queryScalar();

                Yii::info(
                    GelfMessage::create()
                        ->setTimestamp(YII_BEGIN_TIME)
                        ->setShortMessage('Баланс неверный.  ЛС#' . $clientAccountId)
                        ->setFullMessage('Баланс неверный.  ЛС#' . $clientAccountId . ' ' . $billingCounter->amount_date . ' != ' . $lastAccountDate .
                            '(' . $billingCounter->amount_sum . ', ' . $localCounter->amount_sum . ')')
                        ->setAdditional('account_id', $clientAccountId)
                        ->setAdditional('balance', $accountBalance + $localCounter->amount_sum)
                        ->setAdditional('balance_stat', $accountBalance)
                        ->setAdditional('amount_sum', $billingCounter->amount_sum)
                        ->setAdditional('balance_stat_date', $lastAccountDate)
                        ->setAdditional('balance_billing_date', $billingCounter->amount_date)
                        ->setAdditional('is_local', 1),
                    'balance'
                );

                throw new \UnexpectedValueException('Баланс неверный.  ЛС#' . $clientAccountId . ' ' . $billingCounter->amount_date . ' != ' . $lastAccountDate .
                    '(' . $billingCounter->amount_sum . ', ' . $localCounter->amount_sum . ')'
                );
            }

            $localCounter->amount_sum = round($billingCounter->amount_sum, 2);
            $localCounter->amount_day_sum = round($billingCounter->amount_day_sum, 2);
            $localCounter->amount_mn_day_sum = round($billingCounter->amount_mn_day_sum, 2);
            $localCounter->amount_month_sum = round($billingCounter->amount_month_sum, 2);

            $localCounter->sum_w_neg_rate = round($billingCounter->sum_w_neg_rate, 2);
            $localCounter->sum_w_neg_rate_day = round($billingCounter->sum_w_neg_rate_day, 2);
            $localCounter->sum_w_neg_rate_month = round($billingCounter->sum_w_neg_rate_month, 2);

            $localCounter->voice_sum_day = round($billingCounter->voice_sum_day, 2);
            $localCounter->voice_sum_month = round($billingCounter->voice_sum_month, 2);

            $localCounter->data_sum_day = round($billingCounter->data_sum_day, 2);
            $localCounter->data_sum_month = round($billingCounter->data_sum_month, 2);

            $localCounter->sms_sum_day = round($billingCounter->sms_sum_day, 2);
            $localCounter->sms_sum_month = round($billingCounter->sms_sum_month, 2);

            $isNeedSave = false;
            foreach ($localCounter->getDirtyAttributes() as $name => $value) {
                if ($localCounter->isAttributeChanged($name, false)) {
                    $isNeedSave = true;
                }
            }
            $isNeedSave && $localCounter->save();

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

            self::_saveCounterInCache();

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
//                Yii::warning('Баланс массовый не синхронизирован. ЛС#' . $clientAccountId . ' (' . $billingLastBillingDate . ' != ' . $accountLastBillingDate . ') ' . static::$_localCacheFastMass[$clientAccountId]['amount_sum'] . ', ' . $billingCounter['amount_sum']);
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

    private static function _saveCounterInCache()
    {
        $appCache = \Yii::$app->cache;
        foreach (static::$_localCacheFastMass as $accountId => $counter) {
            $appCache->set('bl' . $accountId, $counter, 60 * 7 /* 7 minute (2 * 3)+ */);
        }
    }

}
