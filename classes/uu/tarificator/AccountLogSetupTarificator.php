<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\forms\AccountLogFromToTariff;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use Yii;

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
        // в целях оптимизации удалить слишком старые данные
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
        /** @var AccountLogSetup[] $accountLogs */
        $accountLogs = AccountLogSetup::find()
            ->where('account_tariff_id = :account_tariff_id', [':account_tariff_id' => $accountTariff->id])
            ->indexBy(function (AccountLogSetup $accountLogSetup) {
                return $accountLogSetup->getUniqueId();
            })
            ->all(); // по которым произведен расчет

        $accountTariff->accountTariffLogs;

        $untarificatedPeriods = $accountTariff->getUntarificatedSetupPeriods($accountLogs);
        /** @var AccountLogFromToTariff $untarificatedPeriod */
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $accountLogSetup = $this->getAccountLogSetup($accountTariff, $untarificatedPeriod);
            $accountLogSetup->save();
        }
    }

    /**
     * Создать и вернуть AccountLogPeriod, но не сохранять его!
     * "Не сохранение" нужно для проверки возможности списания без фактического списывания
     *
     * @param AccountTariff $accountTariff
     * @param AccountLogFromToTariff $accountLogFromToTariff
     * @return AccountLogSetup
     */
    public function getAccountLogSetup(AccountTariff $accountTariff, AccountLogFromToTariff $accountLogFromToTariff)
    {
        $tariffPeriod = $accountLogFromToTariff->tariffPeriod;

        $accountLogSetup = new AccountLogSetup();
        $accountLogSetup->date = $accountLogFromToTariff->dateFrom->format('Y-m-d');
        $accountLogSetup->tariff_period_id = $tariffPeriod->id;
        $accountLogSetup->account_tariff_id = $accountTariff->id;

        $accountLogSetup->price_setup = $tariffPeriod->price_setup;
        if ($accountLogFromToTariff->isFirst && $tariffPeriod->tariff->service_type_id == ServiceType::ID_VOIP && $accountTariff->voip_number > 10000 && $accountTariff->number) {
            // телефонный номер кроме телефонной линии (4-5 знаков)
            // только первое подключение. При смене тарифа на том же аккаунте не считать
            $accountLogSetup->price_number = $accountTariff->number->getPrice($tariffPeriod->tariff->currency_id);
        }

        $accountLogSetup->price = $accountLogSetup->price_setup + $accountLogSetup->price_number;
        return $accountLogSetup;
    }
}
