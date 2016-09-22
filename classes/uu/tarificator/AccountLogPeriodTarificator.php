<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\forms\AccountLogFromToTariff;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\helpers\DateTimeZoneHelper;
use RangeException;
use Yii;

/**
 * Предварительное списание (транзакции) абонентской платы. Тарификация
 */
class AccountLogPeriodTarificator implements TarificatorI
{
    /**
     * Рассчитать плату всех услуг
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        AccountLogPeriod::deleteAll(['<', 'date_to', $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT)]);

        $accountTariffs = AccountTariff::find();
        $accountTariffId && $accountTariffs->andWhere(['id' => $accountTariffId]);

        // рассчитать по каждой универсальной услуге
        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from < $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff);
                $isWithTransaction && $transaction->commit();
            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
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
     * @param AccountTariff $accountTariff
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        // по которым произведен расчет
        /** @var AccountLogPeriod[] $accountLogs */
        $accountLogs = AccountLogPeriod::find()
            ->where(['account_tariff_id' => $accountTariff->id])
            ->indexBy(function (AccountLogPeriod $accountLogPeriod) {
                return $accountLogPeriod->getUniqueId();
            })
            ->all();

        $untarificatedPeriods = $accountTariff->getUntarificatedPeriodPeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $accountLogPeriod = $this->getAccountLogPeriod($accountTariff, $untarificatedPeriod);
            $accountLogPeriod->save();
        }
    }

    /**
     * Создать и вернуть AccountLogPeriod, но не сохранять его!
     * "Не сохранение" нужно для проверки возможности списания без фактического списывания
     *
     * @param AccountTariff $accountTariff
     * @param AccountLogFromToTariff $accountLogFromToTariff
     * @return AccountLogPeriod
     */
    public function getAccountLogPeriod(AccountTariff $accountTariff, AccountLogFromToTariff $accountLogFromToTariff)
    {
        $tariffPeriod = $accountLogFromToTariff->tariffPeriod;
        $period = $tariffPeriod->period;

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

        $accountLogPeriod->coefficient = 1 + $accountLogFromToTariff->dateTo
                ->diff($accountLogFromToTariff->dateFrom)
                ->days; // кол-во потраченных дней
        if ($period->monthscount) {
            // разделить на кол-во дней в периоде
            $days = $accountLogFromToTariff->dateFrom
                ->modify($period->getModify())
                ->diff($accountLogFromToTariff->dateFrom)
                ->days;
            $accountLogPeriod->coefficient /= $days;
        }

        if ($tariffPeriod->tariff->getIsTest()) {
            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            // @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
            $accountLogPeriod->price = 0;
        } else {
            $accountLogPeriod->price = $accountLogPeriod->period_price * $accountLogPeriod->coefficient;
        }

        return $accountLogPeriod;
    }
}
