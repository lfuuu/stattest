<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\classes\DateTimeOffsetParams;
use app\modules\uu\models\AccountLogPeriod;

trait AccountTariffBillerPeriodTrait
{
    /** @var DateTimeOffsetParams */
    public $dateOffsetParams;

    /**
     * Вернуть даты периодов, по которым не произведен расчет абонентки
     *
     * @return AccountLogFromToTariff[]
     * @throws \Exception
     * @throws \LogicException
     * @throws ModelValidationException
     * @throws \Throwable
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
            // Это не ошибка. Такое бывает, когда период за сегодня уже пробилинговался, а потом сегодня же сменили тариф. В результате билингуется по новому тарифу. Ну и пусть.
            // throw new \LogicException(sprintf(PHP_EOL . 'There are unknown calculated accountLogPeriod for accountTariffId %d: %s' . PHP_EOL, $this->id, implode(', ', array_keys($accountLogs))));
            foreach ($accountLogs as $accountLog) {
                if (!$accountLog) {
                    continue;
                }

                if (!$accountLog->delete()) {
                    throw new ModelValidationException($accountLog);
                }
            }
        }

        return $untarificatedPeriods;
    }

    /**
     * Устанавливает натсройки смещения времени для получения не тарифицированных периодов (абонентка)
     *
     * @param DateTimeOffsetParams|null $dateTimeOffsetParams
     * @return $this
     */
    public function setDateOffsetParams($dateTimeOffsetParams)
    {
        $this->dateOffsetParams = $dateTimeOffsetParams;

        return $this;
    }

    /**
     * Возвращает текущее время
     *
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    public function getClientDatetimeWithTimezone()
    {
        if ($this->dateOffsetParams) {
            return $this->dateOffsetParams->getClientDateTime($this);
        }

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        return $clientAccount->getDatetimeWithTimezone();
    }
}