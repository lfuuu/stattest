<?php

namespace app\modules\uu\models\traits;

use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogSetup;

trait AccountTariffBillerSetupTrait
{
    /**
     * Вернуть даты периодов, по которым не произведен расчет платы за подключение
     * В отличии от getUntarificatedPeriodPeriods - в периоде учитывается только начало, а не регулярное списание
     *
     * @return AccountLogFromToTariff[]
     * @throws \LogicException
     */
    public function getUntarificatedSetupPeriods()
    {
        // по которым произведен расчет
        /** @var AccountLogSetup[] $accountLogs */
        $accountLogs = AccountLogSetup::find()
            ->where('account_tariff_id = :account_tariff_id', [':account_tariff_id' => $this->id])
            ->indexBy(function (AccountLogSetup $accountLogSetup) {
                return $accountLogSetup->getUniqueId();
            })
            ->all();


        // по которым должен быть произведен расчет
        $untarificatedPeriods = [];
        $minLogDatetime = self::getMinLogDatetime();
        /** @var AccountLogFromToTariff[] $accountLogFromToTariffs */
        $accountLogFromToTariffs = $this->getAccountLogHugeFromToTariffs(); // все


        // по которым не произведен расчет, хотя был должен
        $i = 0; // Порядковый номер нетестового тарифа
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            // @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
            $isTest = $accountLogFromToTariff->tariffPeriod->tariff->getIsTest();
            !$isTest && $i++;

            if ($accountLogFromToTariff->dateFrom < $minLogDatetime) {
                // слишком старый. Для оптимизации считать не будем
                continue;
            }

            $uniqueId = $accountLogFromToTariff->getUniqueId();
            if (isset($accountLogs[$uniqueId])) {
                unset($accountLogs[$uniqueId]);
            } else {
                // этот период не рассчитан
                $accountLogFromToTariff->isFirst = !$isTest && ($i === 1);
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            throw new \LogicException(sprintf(PHP_EOL . 'There are unknown calculated accountLogSetup for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs))));
        }

        return $untarificatedPeriods;
    }
}