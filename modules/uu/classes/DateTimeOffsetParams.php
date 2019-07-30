<?php

namespace app\modules\uu\classes;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\tarificator\AccountLogPeriodTarificator;
use app\modules\uu\tarificator\AccountLogResourceTarificator;
use app\modules\uu\tarificator\Tarificator;
use DateTimeImmutable;

/**
 * Объект сделан для удобства работы со смещениями
 */
class DateTimeOffsetParams
{
    /** @var string */
    public $offsetCurrent = null;

    /** @var string */
    public $offsetClient = null;

    /**
     * Constructor
     * @param Tarificator $rater
     */
    public function __construct(Tarificator $rater = null)
    {
        if (is_null($rater)) {
            return;
        }

        switch (get_class($rater)) {
            case AccountLogPeriodTarificator::class:
                // смещам вперед, чтобы обработать раньше
                $this->offsetCurrent = '+1 hour';
                $this->offsetClient = '+1 hour';

                break;

            case AccountLogResourceTarificator::class:
                // смещаем назад, чтобы выбрать позднее
                $this->offsetCurrent = '-1 hour';
                $this->offsetClient = null;

                break;
        }
    }

    /**
     * Смещает время
     *
     * @param DateTimeImmutable $date
     * @param string|null $offset
     * @return DateTimeImmutable
     */
    protected function modifyDate(DateTimeImmutable $date, $offset)
    {
        if (!$offset) {
            return $date;
        }

        if (DateTimeZoneHelper::isFirstDayOfMonth($date) || DateTimeZoneHelper::isLastDayOfMonth($date)) {
            $date = $date->modify($offset);
        }

        return $date;
    }

    /**
     * Возвращает текущее время
     *
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public function getCurrentDateTime()
    {
        $utcDate = DateTimeZoneHelper::getUtcDateTime();

        return $this->modifyDate($utcDate, $this->offsetCurrent);
    }

    /**
     * Возвращает смещённое время клиента
     *
     * @param AccountTariff $accountTariff
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public function getClientDateTime(AccountTariff $accountTariff)
    {
        $date = $accountTariff->clientAccount->getDatetimeWithTimezone();

        return $this->modifyDate($date, $this->offsetClient);
    }
}