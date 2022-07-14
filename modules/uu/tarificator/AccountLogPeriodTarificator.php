<?php

namespace app\modules\uu\tarificator;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\classes\DateTimeOffsetParams;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\widgets\ConsoleProgress;
use RangeException;
use Yii;

/**
 * Предварительное списание (транзакции) абонентской платы. Тарификация
 */
class AccountLogPeriodTarificator extends Tarificator
{
    public $mode = 1; // 1 - main process, without price package, 2 - only price package

    const DAYS_IN_MONTH = 30.42; // в среднем по всем месяцам (365 / 12)
    const BATCH_READ_SIZE = 500;

    /**
     * Рассчитать плату всех услуг
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     * @throws \Throwable
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $dateTimeOffsetParams = new DateTimeOffsetParams($this);
        $utcDateTime = $dateTimeOffsetParams->getCurrentDateTime();

        $fromId = $toId = null;
        // распаралелливание обработки
        if (isset($_SERVER['argv']) && count($_SERVER['argv']) == 4 && $_SERVER['argv'][1] == 'ubiller/period') {

            $fromId = (int)$_SERVER['argv'][2];
            $toId = (int)$_SERVER['argv'][3];

            if (!$fromId || !$toId || $fromId >= $toId) {
                throw new \InvalidArgumentException('Неверные аргументы');
            }
        }

        $accountTariffQuery = AccountTariff::find()
            ->alias('a')
            ->where(['IS NOT', 'tariff_period_id', null])// только незакрытые (если вчера создали и в тот же день закрыли, то деньги списались через очередь)
            ->andWhere([
                'OR',
                ['account_log_period_utc' => null], // абонентка не списана
                ['<', 'account_log_period_utc', $utcDateTime->format(DateTimeZoneHelper::DATETIME_FORMAT)] // или списана давно
            ]);

        $fromId && $toId && $accountTariffQuery->andWhere(['between', 'a.id', $fromId, $toId]);

        $accountTariffQuery
            ->with('clientAccount')
            ->with('accountLogPeriodsByUniqueKey')
            ->with('accountTariffLogs.accountTariff.clientAccount')
            ->with('accountTariffLogs.tariffPeriod.tariff')
            ->with('accountTariffLogs.tariffPeriod.chargePeriod')
            ->with('tariffPeriod.tariff')
            ->with('number');

//        if (\Yii::$app->isEu()) {
//            $accountTariffQuery
//                ->joinWith('clientAccount as c')
//                ->andWhere(['not', ['c.currency' => Currency::RUB]]);
//        }

        $accountTariffId && $accountTariffQuery->andWhere(['a.id' => $accountTariffId]);

        // рассчитать по каждой универсальной услуге
        $progress = new ConsoleProgress($accountTariffQuery->count(), function ($string) {
            $this->out($string);
        });
        foreach ($accountTariffQuery->each(self::BATCH_READ_SIZE) as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $progress->nextStep();

            if (
                $accountTariff->isPricePackage()
                && $this->mode == 1 // обработка платных пакетов только в mode=2
            ) {
                continue;
            }

            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff, $dateTimeOffsetParams);
                $isWithTransaction && $transaction->commit();
            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                $this->out(PHP_EOL . 'Error. ' . $e->getMessage() . PHP_EOL);
                Yii::error($e->getMessage());
                // не получилось с одной услугой - пойдем считать другую
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     *
     * @param AccountTariff $accountTariff
     * @throws \RangeException
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     * @throws \Throwable
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff, DateTimeOffsetParams $dateTimeOffsetParams = null)
    {
        static $balances = [];
        $maxDateTo = 0;

        $accountTariff->setDateOffsetParams($dateTimeOffsetParams);
        $untarificatedPeriods = $accountTariff->getUntarificatedPeriodPeriods();
        $accountTariff->setDateOffsetParams(null);

        if (!$untarificatedPeriods) {
            return;
        }

        if (!isset($balances[$accountTariff->client_account_id])) {
            $balances[$accountTariff->client_account_id] = $accountTariff->clientAccount->balance + $accountTariff->clientAccount->credit;
        }

        $availBalance = $balances[$accountTariff->client_account_id];
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $accountLogPeriod = $this->getAccountLogPeriod($accountTariff, $untarificatedPeriod);
            $maxDateTo = max($maxDateTo, $accountLogPeriod->date_to);

            if (
                $this->mode == 2
                && $accountLogPeriod->accountTariff
                && $accountLogPeriod->accountTariff->tariff_period_id
                && $accountLogPeriod->accountTariff->isPricePackage()
                && $accountLogPeriod->accountTariff->isLogEditable()
                && ($availBalance - $accountLogPeriod->price) < 0
            ) {
                echo PHP_EOL . 'DP' . $accountTariff->id;
                echo ' (' . $availBalance . ' - ' . $accountLogPeriod->price . ') ';
                // close accountTariff
                $accountTariff->setClosed();
                return;
            }

            if (!$accountLogPeriod->save()) {
                throw new ModelValidationException($accountLogPeriod);
            }

            if (abs($accountLogPeriod->price) >= 0.01) {
                $availBalance -= $accountLogPeriod->price;
                $balances[$accountTariff->client_account_id] -= $accountLogPeriod->price;

                $this->isNeedRecalc = true;
            }
        }

        if ($maxDateTo) {
            // обновить дату, до которой списана абонентка
            $maxDateTimeTo = $accountTariff
                ->clientAccount
                ->getDatetimeWithTimezone($maxDateTo)
                ->setTime(0, 0, 0)
                ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))// перевести в UTC
                ->modify('+1 day'); // "оплачено по" означает "00:00", а нам надо "23:59"

            $accountTariff->account_log_period_utc = $maxDateTimeTo->format(DateTimeZoneHelper::DATETIME_FORMAT);
            if (!$accountTariff->save()) {
                throw new ModelValidationException($accountTariff);
            }
        }
    }

    /**
     * Создать и вернуть AccountLogPeriod, но не сохранять его!
     * "Не сохранение" нужно для проверки возможности списания без фактического списывания
     *
     * @param AccountTariff $accountTariff
     * @param AccountLogFromToTariff $accountLogFromToTariff
     * @return AccountLogPeriod
     * @throws \RangeException
     */
    public function getAccountLogPeriod(AccountTariff $accountTariff, AccountLogFromToTariff $accountLogFromToTariff)
    {
        $tariffPeriod = $accountLogFromToTariff->tariffPeriod;
        $tariff = $tariffPeriod->tariff;

        $accountLogPeriod = new AccountLogPeriod();
        $accountLogPeriod->date_from = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogPeriod->date_to = $accountLogFromToTariff->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
        if ($accountLogFromToTariff->dateTo < $accountLogFromToTariff->dateFrom) {
            throw new RangeException(sprintf('Date_to %s can not be less than date_from %s. AccountTariffId = %d',
                $accountLogPeriod->date_to, $accountLogPeriod->date_from, $accountTariff->id));
        }

        $accountLogPeriod->tariff_period_id = $tariffPeriod->id;
        $accountLogPeriod->account_tariff_id = $accountTariff->id;
        $accountLogPeriod->populateRelation('accountTariff', $accountTariff);
        $accountLogPeriod->period_price = $tariffPeriod->price_per_period;

        $totalDays = 1 + $accountLogFromToTariff->dateTo
                ->diff($accountLogFromToTariff->dateFrom)
                ->days; // кол-во потраченных дней

        if ($tariff->count_of_carry_period || !$tariff->is_proportionately) {

            // Если "Пакет интернета сгорает через N месяцев", то списывается полностью. И трафик дается тоже полностью
            $accountLogPeriod->coefficient = 1;

        } elseif ($totalDays > 31) {

            // больше месяца (при оплате за квартал, полгода, год)
            // этот метод вызывается из
            // ... основного биллинга. Все периоды уже и так разбиты по месяцам, поэтому сюда не попадем
            // ... из валидации при создании "хватит ли денег". Тут большая точность не обязательно, ибо фактического списания не происходит. Достаточно "средней температуры по больнице"
            $accountLogPeriod->coefficient = round($totalDays / self::DAYS_IN_MONTH);

        } else {

            // месяц или меньше - разделить на кол-во дней в месяце
            $daysInMonth = $accountLogFromToTariff->dateFrom
                ->modify('+1 month')
                ->diff($accountLogFromToTariff->dateFrom)
                ->days;
            $accountLogPeriod->coefficient = $totalDays / $daysInMonth;
        }

        if ($tariff->service_type_id === ServiceType::ID_INFRASTRUCTURE) {
            // инфраструктура - цена берется из услуги!
            $accountLogPeriod->price = $accountTariff->price * $accountLogPeriod->coefficient;

        } elseif ($tariff->getIsTest()) {
            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            $accountLogPeriod->price = 0;

        } else {
            $accountLogPeriod->price = $accountLogPeriod->period_price * $accountLogPeriod->coefficient;
        }

        return $accountLogPeriod;
    }
}
