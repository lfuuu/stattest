<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use DateTimeImmutable;
use Exception;
use yii\db\Expression;
use yii\db\Query;

trait AccountTariffBillerTrait
{
    private static $_isFullTarification = false;

    /**
     * @param bool $isFullTarification
     */
    public static function setIsFullTarification($isFullTarification)
    {
        self::$_isFullTarification = $isFullTarification;
    }

    /**
     * Вернуть дату, с которой рассчитываем лог при полной проверке. Если date_from строго меньше этой даты, то этот период не нужен в расчете
     * Это нужно для оптимизации, чтобы не хранить много лишних данных, которые не нужны, а только тормозят расчет новых
     *
     * @return DateTimeImmutable
     * @throws Exception
     */
    public static function getMinLogDatetime()
    {
        // за этот и предыдущий месяц
        // за предыдущий вообще-то не нужны, но пусть будут, чтобы можно было аргументированно отвечать на претензии клиентов
        return DateTimeZoneHelper::getUtcDateTime()
            ->setTime(0, 0, 0)
            ->modify('first day of previous month');
    }

    /**
     * Вернуть дату, с которой рассчитываем подключение при сокращенной проверке.
     * Для ускорения пересчета более старые смены тарифов не учитываем (но и не удаляем).
     * На расчет минималки, абонентки и ресурсов это не влияет.
     *
     * @return DateTimeImmutable
     * @throws Exception
     */
    public static function getMinSetupDatetime()
    {
        return DateTimeZoneHelper::getUtcDateTime()
            ->setTime(0, 0, 0)
            ->modify('-2 days');
    }

    /**
     * Вернуть лог уникальных тарифов
     * В отличие от $this->accountTariffLogs
     *  - только те, которые не переопределены другим до наступления этой даты
     *  - в порядке возрастания
     *  - только активные на данный момент
     *
     * @param bool $isWithFuture
     * @return AccountTariffLog[]
     */
    public function getUniqueAccountTariffLogs($isWithFuture = false)
    {
        $accountTariffLogs = [];
        /** @var AccountTariffLog $accountTariffLogPrev */
        $accountTariffLogPrev = null;

        $clientDate = $this
            ->getClientDatetimeWithTimezone()
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var AccountTariffLog $accountTariffLog */
        foreach ($this->accountTariffLogs as $accountTariffLog) {
            if ($accountTariffLogPrev &&
                $accountTariffLogPrev->actual_from == $accountTariffLog->actual_from &&
                $accountTariffLogPrev->actual_from_utc > $accountTariffLogPrev->insert_time // строго раньше наступления даты
            ) {
                // неактивный тариф, потому что переопределен другим до наступления этой даты
                // если переопределен в тот же день, то списываем за оба
                continue;
            }

            if (!$isWithFuture && $accountTariffLog->actual_from > $clientDate) {
                // еще не наступил
                continue;
            }

            $accountTariffLogs[$accountTariffLog->getUniqueId()] = $accountTariffLogPrev = $accountTariffLog;
        }

        $accountTariffLogs = array_reverse($accountTariffLogs, true); // по возрастанию. Это важно для расчета периодов и цен
        return $accountTariffLogs;
    }

    /**
     * Вернуть большие периоды, разбитые только по смене тарифов
     * У последнего тарифа dateTo может быть null (не ограничен по времени)
     *
     * @param bool $isWithFuture
     * @param Period $chargePeriodMain если указано, то использовать указанное, а не из tariffPeriod
     * @return AccountLogFromToTariff[]|array
     * @throws Exception
     */
    public function getAccountLogHugeFromToTariffs($isWithFuture = false, $chargePeriodMain = null)
    {
        /** @var AccountLogFromToTariff[] $accountLogPeriods */
        $accountLogPeriods = [];
        $uniqueAccountTariffLogs = $this->getUniqueAccountTariffLogs($isWithFuture);
        foreach ($uniqueAccountTariffLogs as $uniqueAccountTariffLog) {

            // начало нового периода
            $dateActualFrom = new DateTimeImmutable($uniqueAccountTariffLog->actual_from);

            if (($count = count($accountLogPeriods)) > 0) {

                // закончить предыдущий период
                $prevAccountTariffLog = $accountLogPeriods[($count - 1)];
                $prevTariffPeriodChargePeriod = $chargePeriodMain ?: $prevAccountTariffLog->tariffPeriod->chargePeriod;

                // старый тариф должен закончиться не раньше этой даты
                $dateActualFromYmd = $dateActualFrom->format(DateTimeZoneHelper::DATE_FORMAT);
                $insertTimeYmd = (new DateTimeImmutable($uniqueAccountTariffLog->insert_time))->format(DateTimeZoneHelper::DATE_FORMAT);
                if ($dateActualFromYmd < $insertTimeYmd) {

                    $isPackage = array_key_exists($this->service_type_id, ServiceType::$packages);
                    if ($isPackage && (!$uniqueAccountTariffLog->tariffPeriod || $uniqueAccountTariffLog->tariffPeriod->tariff->isTest)) {
                        // если нельзя, но очень хочется, то можно
                        // пакеты по умолчанию подключаются/отключаются автоматически. Им можно всё
                        $insertTimeYmd = UsageInterface::MIN_DATE;
                    } else {
                        throw new \LogicException('Тариф нельзя менять задним числом: ' . $uniqueAccountTariffLog->id);
                    }
                }

                /** @var DateTimeImmutable $dateFromNext дата теоретического начала (продолжения) старого тарифа. Из нее -1day получается дата окончания его прошлого периода */
                if ($dateActualFromYmd == $insertTimeYmd) {
                    // если смена произошла в тот же день, то этот день билингуется дважды: по старому тарифу (с полуночи до insert_time, но не менее периода списания) и по новому (с insert_time)
                    $dateTimeMin = $dateActualFrom;
                } else {
                    $dateTimeMin = $dateActualFrom->modify('-1 day');
                }

                unset($dateActualFromYmd, $insertTimeYmd);

                $prevAccountTariffLog->dateTo = $prevTariffPeriodChargePeriod->getMaxDateTo($prevAccountTariffLog->dateFrom, $dateTimeMin);
            }

            if (!$uniqueAccountTariffLog->tariffPeriod) {
                // услуга закрыта
                break;
            }

            // начать новый период
            $accountLogPeriods[] = new AccountLogFromToTariff();

            $count = count($accountLogPeriods);
            $accountLogPeriods[$count - 1]->dateFrom = $dateActualFrom;
            $accountLogPeriods[$count - 1]->tariffPeriod = $uniqueAccountTariffLog->tariffPeriod;
        }

        return $accountLogPeriods;
    }

    /**
     * Вернуть периоды, разбитые не более периода списания
     * Разбиты по логу тарифов, периодам списания, 1-м числам месяца.
     *
     * @param Period $chargePeriodMain если указано, то использовать указанное, а не из getAccountLogHugeFromToTariffs
     * @param bool $isWithCurrent возвращать ли незаконченный (длится еще) тариф? Для предоплаты надо, для постоплаты нет
     * @param bool $isSplitByMonth делить ли по календарным месяцам (для бухгалтерии) или нет
     * @return AccountLogFromToTariff[]
     * @throws \LogicException
     * @throws \Exception
     */
    public function getAccountLogFromToTariffs($chargePeriodMain = null, $isWithCurrent = true, $isSplitByMonth = true)
    {
        /** @var AccountLogFromToTariff[] $accountLogPeriods */
        $accountLogPeriods = [];
        $dateTo = $dateFrom = null;
        $minLogDatetime = self::getMinLogDatetime();

        $dateTimeNow = $this->getClientDatetimeWithTimezone();

        // взять большие периоды, разбитые только по смене тарифов
        // и разбить по периодам списания и первым числам
        $accountLogHugePeriods = $this->getAccountLogHugeFromToTariffs($isWithFuture = false, $chargePeriodMain);
        foreach ($accountLogHugePeriods as $accountLogHugePeriod) {

            $dateTo = $accountLogHugePeriod->dateTo;
            if ($dateTo && $dateTo < $minLogDatetime) {
                // слишком старый. Для оптимизации считать не будем
                continue;
            }

            $tariffPeriod = $accountLogHugePeriod->tariffPeriod;
            /** @var Period $chargePeriod */
            $chargePeriod = $chargePeriodMain ?: $tariffPeriod->chargePeriod;
            $dateFrom = $accountLogHugePeriod->dateFrom;
            if ($dateTo) {
                $dateToLimited = $dateTo;
            } else {
                // текущий день по таймзоне ЛС
                $dateToLimited = $chargePeriod->getMaxDateTo($dateFrom, $dateTimeNow);
                unset($timezoneName, $timezone);
            }

            do {
                $accountLogPeriod = new AccountLogFromToTariff();
                $accountLogPeriod->tariffPeriod = $tariffPeriod;
                $accountLogPeriod->dateFrom = $dateFrom;

                $accountLogPeriod->dateTo = $dateFrom;

                if ($tariffPeriod->tariff->is_one_alt) {
                    $accountLogPeriod->dateTo = $accountLogPeriod->dateTo->setDate(3000,1,1); // Period::OPEN_DATE
                } else if ($chargePeriod->monthscount) {
                    if (!$isSplitByMonth && $chargePeriod->monthscount > 1) {
                        $accountLogPeriod->dateTo = $accountLogPeriod->dateTo->modify('+' . ($chargePeriod->monthscount - 1) . ' months');
                    }

                    $accountLogPeriod->dateTo = $accountLogPeriod->dateTo->modify('last day of this month');
                }

                if ($accountLogPeriod->dateTo >= $minLogDatetime) {
                    // Для оптимизации считаем только нестарые
                    $accountLogPeriods[] = $accountLogPeriod;
                }

                // начать новый период
                /** @var DateTimeImmutable $dateFrom */
                $dateFrom = $accountLogPeriod->dateTo->modify('+1 day');

            } while ($dateFrom->format(DateTimeZoneHelper::DATE_FORMAT) <= $dateToLimited->format(DateTimeZoneHelper::DATE_FORMAT));

        }

        if (!$isWithCurrent &&
            ($count = count($accountLogPeriods)) &&
            !$dateTo && $dateFrom > (new DateTimeImmutable())
        ) {
            // если count, то $dateTo и $dateFrom определены
            // если тариф действующий (!$dateTo) и следующий должен начаться не сегодня ($dateFrom > (new DateTimeImmutable()))
            // значит, последний период еще длится - удалить из расчета
            unset($accountLogPeriods[($count - 1)]);
            return $accountLogPeriods;
        }

        return $accountLogPeriods;
    }

    /**
     * Закрыть услугу
     *
     * @param null $actualFromUtc
     * @throws \yii\db\Exception
     */
    public function setClosed($actualFromUtc = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $nextAccountTariffLog = new AccountTariffLog();
        $actualFromUtc = $actualFromUtc ?: (new Query)->select(new Expression('UTC_TIMESTAMP()'))->scalar();
        try {
            $nextAccountTariffLog->account_tariff_id = $this->id;
            $nextAccountTariffLog->tariff_period_id = null;
            $nextAccountTariffLog->actual_from_utc = $actualFromUtc;
            if (!$nextAccountTariffLog->save()) {
                throw new ModelValidationException($nextAccountTariffLog);
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}