<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogPeriod;

trait AccountTariffBillerPeriodTrait
{
    /**
     * Вернуть даты периодов, по которым не произведен расчет абонентки
     *
     * @return AccountLogFromToTariff[]
     * @throws \LogicException
     * @throws ModelValidationException
     */
    public function getUntarificatedPeriodPeriods()
    {
        // по которым произведен расчет
        /** @var AccountLogPeriod[] $accountLogs */
        $accountLogs = AccountLogPeriod::find()
            ->where(['account_tariff_id' => $this->id])
            ->indexBy(function (AccountLogPeriod $accountLogPeriod) {
                return $accountLogPeriod->getUniqueId();
            })
            ->all();

        // по которым должен быть произведен расчет
        /** @var AccountLogFromToTariff[] $accountLogFromToTariffs */
        $accountLogFromToTariffs = $this->getAccountLogFromToTariffs($chargePeriodMain = null, $isWithCurrent = true); // все


        // по которым не произведен расчет, хотя был должен
        $untarificatedPeriods = [];
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            $uniqueId = $accountLogFromToTariff->getUniqueId();
            if (isset($accountLogs[$uniqueId])) {
                // такой период рассчитан
                // проверим, все ли корректно
                $accountLog = $accountLogs[$uniqueId];
                $dateToTmp = $accountLogFromToTariff->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
                if ($accountLog->date_to !== $dateToTmp) {
                    throw new \LogicException(sprintf('Calculated accountLogPeriod date %s is not equal %s for accountTariffId %d', $accountLog->date_to, $dateToTmp, $this->id));
                }

                $tariffPeriodId = $accountLogFromToTariff->tariffPeriod->id;
                if ($accountLog->tariff_period_id !== $tariffPeriodId) {
                    throw new \LogicException(sprintf('Calculated accountLogPeriod %s is not equal %s for accountTariffId %d', $accountLog->tariff_period_id, $tariffPeriodId, $this->id));
                }

                unset($accountLogs[$uniqueId]);

            } else {
                // этот период не рассчитан
                $untarificatedPeriods[] = $accountLogFromToTariff;
            }
        }

        if (count($accountLogs)) {
            // остался неизвестный период, который уже рассчитан
            throw new \LogicException(sprintf(PHP_EOL . 'There are unknown calculated accountLogPeriod for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs))));
        }

        return $untarificatedPeriods;
    }
}