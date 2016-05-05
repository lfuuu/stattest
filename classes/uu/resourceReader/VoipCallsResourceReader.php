<?php

namespace app\classes\uu\resourceReader;

use app\classes\DateTimeWithUserTimezone;
use app\classes\uu\model\AccountTariff;
use app\models\billing\CallsAggr;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\Object;

class VoipCallsResourceReader extends Object implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    protected $dateToValue = [];
    protected $usageVoipId = null;

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $usageVoipId = $accountTariff->getNonUniversalId();

        $dateStr = $dateTime->format('Y-m-d');
        if ($this->usageVoipId === $usageVoipId) {
            // для этой услуги уже есть кэш
            if (isset($this->dateToValue[$dateStr])) {
                return $this->dateToValue[$dateStr]['sum_cost'];
            } else {
                return 0; // если нет звонков, то это нормально, поэтому 0, а не null
            }
        }

        $this->usageVoipId = $usageVoipId;

        // в БД хранится в UTC, но считать надо в зависимости от таймзоны клиента
        $clientDateTimeZone = $accountTariff->clientAccount->getTimezone();
        $utcDateTimeZone = new DateTimeZone(DateTimeWithUserTimezone::TIMEZONE_DEFAULT);
        $hoursDelta = (int)(
                $clientDateTimeZone->getOffset($dateTime) -
                $utcDateTimeZone->getOffset($dateTime)
            ) / 3600; // таймзона клиента в часах относительно UTC


        // этот метод вызывается в цикле по услуге, внутри в цикле по возрастанию даты.
        // Поэтому надо кэшировать по одной услуге все даты в будущем, сгруппированные до суткам в таймзоне клиента
        $this->dateToValue = CallsAggr::find()
            ->select([
                'sum_cost' => 'SUM(cost) * -1', // в CallsAggr стоимость отрицательная, что означает "списание". А в AccountLogResource это должно быть положительным
                'aggr_date' => sprintf("TO_CHAR(aggr_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
            ])
            ->where(['number_service_id' => $usageVoipId])
            ->andWhere(sprintf("aggr_time + INTERVAL '%d hours' >= :date", $hoursDelta), [':date' => $dateTime->format(DATE_ATOM)])
            ->groupBy('aggr_date')
            ->indexBy('aggr_date')
            ->asArray()
            ->all();

        if (isset($this->dateToValue[$dateStr])) {
            return $this->dateToValue[$dateStr]['sum_cost'];
        } else {
            return 0; // если нет звонков, то это нормально, поэтому 0, а не null
        }

    }

    /**
     * Как считать PricePerUnit - указана за месяц или за день
     * true - за месяц (при ежедневном расчете надо разделить на кол-во дней в месяце)
     * false - за день (при ежедневном расчете так и оставить)
     * @return bool
     */
    public function getIsMonthPricePerUnit()
    {
        return false;
    }
}