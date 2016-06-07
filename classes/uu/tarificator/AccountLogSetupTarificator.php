<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;

/**
 * Предварительное списание (транзакции) стоимости подключения. Тарификация
 */
class AccountLogSetupTarificator
{
    /**
     * Рассчитать плату всех услуг
     */
    public function tarificateAll()
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        AccountLogSetup::deleteAll(['<', 'date', $minLogDatetime->format('Y-m-d')]);

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

            $this->tarificateAccountTariff($accountTariff);
        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     * @param AccountTariff $accountTariff
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        /** @var AccountLogSetup[] $accountLogs */
        $accountLogs = AccountLogSetup::find()
            ->where('account_tariff_id = :account_tariff_id', [':account_tariff_id' => $accountTariff->id])
            ->indexBy('date')
            ->all(); // по которым произведен расчет

        $untarificatedPeriods = $accountTariff->getUntarificatedSetupPeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $tariffPeriod = $untarificatedPeriod->getTariffPeriod();

            $accountLogSetup = new AccountLogSetup();
            $accountLogSetup->date = $untarificatedPeriod->getDateFrom()->format('Y-m-d');
            $accountLogSetup->tariff_period_id = $tariffPeriod->id;
            $accountLogSetup->account_tariff_id = $accountTariff->id;
            $accountLogSetup->price = $tariffPeriod->price_setup;
            $accountLogSetup->save();
        }
    }
}
