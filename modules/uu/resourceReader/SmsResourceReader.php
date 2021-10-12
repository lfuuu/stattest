<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\SmscRaw;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\BaseObject;

/**
 * Class SmsResourceReader
 * @package app\modules\uu\resourceReader
 *
 * @property bool $isMonthPricePerUnit
 */
class SmsResourceReader extends BaseObject implements ResourceReaderInterface
{
    protected $accountTariffId = null;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return Amounts
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        if ($this->accountTariffId !== $accountTariff->id) {
            $this->setDateToValue($accountTariff, $dateTime);
        }

        $dateTimeFormat = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var integer $amount количество штук СМС */
        $amount = array_key_exists($dateTimeFormat, $this->cache) ?
            $this->cache[$dateTimeFormat] : 0;

        return new Amounts($amount, 0);
    }

    /**
     * Построить кэш по этой услуге
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     */
    protected function setDateToValue(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $this->accountTariffId = $accountTariff->id;

        // в БД хранится в UTC, но считать надо в зависимости от таймзоны клиента
        $clientDateTimeZone = $accountTariff->clientAccount->getTimezone();
        $utcDateTimeZone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        $hoursDelta = (int)(
                $clientDateTimeZone->getOffset($dateTime) -
                $utcDateTimeZone->getOffset($dateTime)
            ) / 3600; // таймзона клиента в часах относительно UTC

        if ($hoursDelta >= 0) {
            $dateTimeUtc = $dateTime->modify('-' . $hoursDelta . ' hours');
        } else {
            $dateTimeUtc = $dateTime->modify('+' . abs($hoursDelta) . ' hours');
        }


        // этот метод вызывается в цикле по услуге, внутри в цикле по возрастанию даты.
        // Поэтому надо кэшировать по одной услуге все даты в будущем, сгруппированные до суткам в таймзоне клиента
        $this->cache = SmscRaw::find()
            ->alias('c')
            ->innerJoinWith('accountTariffLight l')
            ->select([
                'sum' => 'SUM(ABS(c.cost))',
                'aggr_date' => sprintf("TO_CHAR(c.setup_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta),
            ])
            ->where([
                'c.src_number' => (string)$accountTariff->prevAccountTariff->voip_number,
                'c.account_id' => $accountTariff->client_account_id,
                'l.account_package_id' => $accountTariff->id
            ])
            ->andWhere(['>=', 'c.setup_time', $dateTimeUtc->format(DATE_ATOM)])
            ->groupBy(['aggr_date'])
            ->asArray()
            ->indexBy('aggr_date')
            ->column();
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