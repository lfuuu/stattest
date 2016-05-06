<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use RangeException;

/**
 * Предварительное списание (транзакции) абонентской платы. Тарификация
 */
class AccountLogPeriodTarificator
{
    /**
     * Рассчитать плату всех услуг
     */
    public function tarificateAll()
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        AccountLogPeriod::deleteAll(['<', 'date_to', $minLogDatetime->format('Y-m-d')]);

        $accountTariffs = AccountTariff::find();

        // рассчитать по каждой универсальной услуге
        foreach ($accountTariffs->each() as $accountTariff) {
            echo '. ';

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from < $minLogDatetime->format('Y-m-d'))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            $this->tarificateAccountTariff($accountTariff);
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
            ->indexBy('date_from')
            ->all();

        $untarificatedPeriods = $accountTariff->getUntarificatedPeriodPeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $tariffPeriod = $untarificatedPeriod->getTariffPeriod();
            $period = $tariffPeriod->period;

            $accountLogPeriod = new AccountLogPeriod();
            $accountLogPeriod->date_from = $untarificatedPeriod->getDateFrom()->format('Y-m-d');
            $accountLogPeriod->date_to = $untarificatedPeriod->getDateTo()->format('Y-m-d');
            if ($untarificatedPeriod->getDateTo() < $untarificatedPeriod->getDateFrom()) {
                throw new RangeException(sprintf('Date_to %s can not be less than date_from %s. AccountTariffId = %d',
                    $accountLogPeriod->date_to, $accountLogPeriod->date_from, $accountTariff->id));
            }

            $accountLogPeriod->tariff_period_id = $tariffPeriod->id;
            $accountLogPeriod->account_tariff_id = $accountTariff->id;
            $accountLogPeriod->period_price = $tariffPeriod->price_per_period;
            $accountLogPeriod->coefficient = 1 + $untarificatedPeriod->getDateTo()
                    ->diff($untarificatedPeriod->getDateFrom())
                    ->days; // кол-во потраченных дней
            if ($period->monthscount) {
                // разделить на кол-во дней в периоде
                $days = 1 + $untarificatedPeriod->getDateFrom()
                        ->modify($period->getModify())
                        ->modify('-1 day')
                        ->diff($untarificatedPeriod->getDateFrom())
                        ->days;
                $accountLogPeriod->coefficient /= $days;
            }
            $accountLogPeriod->price = $accountLogPeriod->period_price * $accountLogPeriod->coefficient;
            $accountLogPeriod->save();
        }
    }
}
