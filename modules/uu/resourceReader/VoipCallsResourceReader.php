<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsAggr;
use app\modules\uu\models\AccountTariff;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\Object;

class VoipCallsResourceReader extends Object implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    protected $dateToValue = [];

    /**
     * @var string День (Y-m-d) последнего расчета данных в БД по таймзоне клиента. То есть за этот день еще не все рассчитано, его не надо учитывать! Только строго меньше
     * Если до этой даты нет данных - звонков не было, если после - неизвестно, надо посчитать позже
     */
    protected $maxCalculatedDate = null;

    protected $accountTariffId = null;

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        if ($this->accountTariffId === $accountTariff->id) {
            // для этой услуги уже есть кэш
            if ($this->maxCalculatedDate <= $date) {
                return null; // нет данных
            } elseif (isset($this->dateToValue[$date])) {
                return $this->dateToValue[$date];
            } else {
                return 0; // нет звонков
            }
        }

        $this->accountTariffId = $accountTariff->id;

        // в БД хранится в UTC, но считать надо в зависимости от таймзоны клиента
        $clientDateTimeZone = $accountTariff->clientAccount->getTimezone();
        $utcDateTimeZone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        $hoursDelta = (int)(
                $clientDateTimeZone->getOffset($dateTime) -
                $utcDateTimeZone->getOffset($dateTime)
            ) / 3600; // таймзона клиента в часах относительно UTC


        // Дата актуальности данных в БД
        $this->maxCalculatedDate = CallsAggr::find()
            ->select([
                'max_aggr_time' => sprintf("TO_CHAR(MAX(aggr_time) + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta),
            ])
            ->scalar();

        // этот метод вызывается в цикле по услуге, внутри в цикле по возрастанию даты.
        // Поэтому надо кэшировать по одной услуге все даты в будущем, сгруппированные до суткам в таймзоне клиента
        $this->dateToValue = CallsAggr::find()
            ->select([
                // в CallsAggr стоимость отрицательная, что означает "списание". А в AccountLogResource это должно быть положительным
                'sum_cost' => 'SUM(cost) * -1',
                'aggr_date' => sprintf("TO_CHAR(aggr_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
            ])
            ->where(['number_service_id' => $accountTariff->id])
            ->andWhere(sprintf("aggr_time + INTERVAL '%d hours' >= :date", $hoursDelta),
                [':date' => $dateTime->format(DATE_ATOM)])
            ->groupBy('aggr_date')
            ->indexBy('aggr_date')
            ->column();

        if ($this->maxCalculatedDate <= $date) {
            Yii::error(sprintf('VoipCallsResourceReader. Нет данных по ресурсу. AccountTariffId = %d, дата = %s.', $accountTariff->id, $date));
            return null; // нет данных
        } elseif (isset($this->dateToValue[$date])) {
            return $this->dateToValue[$date];
        } else {
            return 0; // нет звонков
        }

    }

    /**
     * Как считать PricePerUnit - указана за месяц или за день
     * true - за месяц (при ежедневном расчете надо разделить на кол-во дней в месяце)
     * false - за день (при ежедневном расчете так и оставить)
     *
     * @return bool
     */
    public function getIsMonthPricePerUnit()
    {
        return false;
    }
}