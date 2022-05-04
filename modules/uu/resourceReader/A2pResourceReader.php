<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\A2pSms;
use app\models\billing\SmscRaw;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\BaseObject;
use yii\db\ActiveQuery;

/**
 * Class A2pResourceReader
 * @package app\modules\uu\resourceReader
 *
 * @property bool $isMonthPricePerUnit
 */
class A2pResourceReader extends BaseObject implements ResourceReaderInterface
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

        $dateTimeUtc = $dateTime->modify(($hoursDelta >= 0 ? '-' : '+') . abs($hoursDelta) . ' hours');

        $this->cache = $this->getSmscStat($hoursDelta, $dateTimeUtc, $accountTariff);
        if (!$this->cache) {
            $this->cache = $this->getA2pStat($hoursDelta, $dateTimeUtc, $accountTariff);
        }
    }

    private function getSmscStat($hoursDelta, $dateTimeUtc, $accountTariff)
    {
        return $this->getStat(SmscRaw::find(), 'c.setup_time', $hoursDelta, $dateTimeUtc, $accountTariff);
    }

    private function getA2pStat($hoursDelta, $dateTimeUtc, $accountTariff)
    {
        return $this->getStat(A2pSms::find(), 'c.charge_time', $hoursDelta, $dateTimeUtc, $accountTariff);
    }

    private function getStat(ActiveQuery $query, $timeField, $hoursDelta, $dateTimeUtc, $accountTariff)
    {
        return
            $query
                ->alias('c')
                ->innerJoinWith('accountTariffLight l')
                ->select([
                    'sum' => 'SUM(ABS(c.cost))',
                    'aggr_date' => sprintf("TO_CHAR(%s + INTERVAL '%d hours', 'YYYY-MM-DD')", $timeField, $hoursDelta),
                ])
                ->where([
                    'c.account_id' => $accountTariff->client_account_id,
                    'l.account_package_id' => $accountTariff->id
                ])
                ->andWhere(['>=', $timeField, $dateTimeUtc->format(DATE_ATOM)])
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