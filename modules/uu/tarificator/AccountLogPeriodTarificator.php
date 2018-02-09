<?php

namespace app\modules\uu\tarificator;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use RangeException;
use Yii;

/**
 * Предварительное списание (транзакции) абонентской платы. Тарификация
 */
class AccountLogPeriodTarificator extends Tarificator
{
    const DAYS_IN_MONTH = 30.42; // в среднем по всем месяцам (365 / 12)

    /**
     * Рассчитать плату всех услуг
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        $now = date(DateTimeZoneHelper::DATE_FORMAT);

        // в целях оптимизации удалить старые данные
        if (!$accountTariffId) {
            AccountLogPeriod::deleteAll(['<', 'date_to', $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT)], [], 'id ASC');
        }

        $accountTariffs = AccountTariff::find();
        $accountTariffId && $accountTariffs->andWhere(['id' => $accountTariffId]);

        // рассчитать по каждой универсальной услуге
        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                $this->out('. ');
            }

            /** @var AccountTariff $accountTariff */
            $accountTariffLog = $accountTariff->getAccountTariffLogs()->one();
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from_utc < $minLogDatetime->format(DateTimeZoneHelper::DATETIME_FORMAT))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                // @todo денормализовать услугу, чтобы хранить там эту дату и не лазить каждый раз в лог
                continue;
            }

            if (!$this->isFullTarification) {
                // сокращенная проверка
                $accountLogPeriodDateTo = $accountTariff->getAccountLogPeriods()
                    ->select(['date_to'])
                    ->orderBy(['id' => SORT_DESC])
                    ->scalar();
                if ($accountLogPeriodDateTo && $accountLogPeriodDateTo > $now) {
                    // уже есть списание абонентки на будущее. Дальше списывать нет необходимости
                    // сравнение строго больше, чтобы не зависеть от таймзоны
                    // @todo денормализовать услугу, чтобы хранить там эту дату и не лазить каждый раз в лог
                    continue;
                }
            }

            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff);
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
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        $untarificatedPeriods = $accountTariff->getUntarificatedPeriodPeriods();
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $accountLogPeriod = $this->getAccountLogPeriod($accountTariff, $untarificatedPeriod);
            if (!$accountLogPeriod->save()) {
                throw new ModelValidationException($accountLogPeriod);
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

        $accountLogPeriod = new AccountLogPeriod();
        $accountLogPeriod->date_from = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogPeriod->date_to = $accountLogFromToTariff->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
        if ($accountLogFromToTariff->dateTo < $accountLogFromToTariff->dateFrom) {
            throw new RangeException(sprintf('Date_to %s can not be less than date_from %s. AccountTariffId = %d',
                $accountLogPeriod->date_to, $accountLogPeriod->date_from, $accountTariff->id));
        }

        $accountLogPeriod->tariff_period_id = $tariffPeriod->id;
        $accountLogPeriod->account_tariff_id = $accountTariff->id;
        $accountLogPeriod->period_price = $tariffPeriod->price_per_period;

        $totalDays = 1 + $accountLogFromToTariff->dateTo
                ->diff($accountLogFromToTariff->dateFrom)
                ->days; // кол-во потраченных дней

        if ($totalDays > 31) {
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

        if ($tariffPeriod->tariff->service_type_id === ServiceType::ID_INFRASTRUCTURE) {
            // инфраструктура - цена берется из услуги!
            $accountLogPeriod->price = $accountTariff->price * $accountLogPeriod->coefficient;

        } elseif ($tariffPeriod->tariff->getIsTest()) {
            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            // @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
            $accountLogPeriod->price = 0;

        } else {
            $accountLogPeriod->price = $accountLogPeriod->period_price * $accountLogPeriod->coefficient;
        }

        return $accountLogPeriod;
    }
}
