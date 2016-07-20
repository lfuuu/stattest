<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use RangeException;
use Yii;

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
        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from < $minLogDatetime->format('Y-m-d'))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
                Yii::error($e->getMessage());
                // не получилось с одной услугой - пойдем считать другую
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
            $tariffPeriod = $untarificatedPeriod->tariffPeriod;
            $period = $tariffPeriod->period;

            $accountLogPeriod = new AccountLogPeriod();
            $accountLogPeriod->date_from = $untarificatedPeriod->dateFrom->format('Y-m-d');
            $accountLogPeriod->date_to = $untarificatedPeriod->dateTo->format('Y-m-d');
            if ($untarificatedPeriod->dateTo < $untarificatedPeriod->dateFrom) {
                throw new RangeException(sprintf('Date_to %s can not be less than date_from %s. AccountTariffId = %d',
                    $accountLogPeriod->date_to, $accountLogPeriod->date_from, $accountTariff->id));
            }

            $accountLogPeriod->tariff_period_id = $tariffPeriod->id;
            $accountLogPeriod->account_tariff_id = $accountTariff->id;
            $accountLogPeriod->period_price = $tariffPeriod->price_per_period;
            $accountLogPeriod->coefficient = 1 + $untarificatedPeriod->dateTo
                    ->diff($untarificatedPeriod->dateFrom)
                    ->days; // кол-во потраченных дней
            if ($period->monthscount) {
                // разделить на кол-во дней в периоде
                $days = 1 + $untarificatedPeriod->dateFrom
                        ->modify($period->getModify())
                        ->modify('-1 day')
                        ->diff($untarificatedPeriod->dateFrom)
                        ->days;
                $accountLogPeriod->coefficient /= $days;
            }
            $accountLogPeriod->price = $accountLogPeriod->period_price * $accountLogPeriod->coefficient;
            $accountLogPeriod->save();
        }
    }
}
