<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\mtt_raw\MttRaw;
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
    const COST_AMOUNT = 0.47; // Себестоимость MTT без НДС, руб

    private $_accountTariffId = null;

    /**
     * @var array
     */
    private $_cache = [];

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
        if ($this->_accountTariffId !== $accountTariff->prev_account_tariff_id) {
            $this->_setDateToValue($accountTariff, $dateTime);
        }

        $dateTimeFormat = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var integer $amount количество штук СМС */
        $amount = array_key_exists($dateTimeFormat, $this->_cache) ?
            $this->_cache[$dateTimeFormat] : 0;

        return new Amounts($amount, $amount * self::COST_AMOUNT);
    }

    /**
     * Построить кэш по этой услуге
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     */
    private function _setDateToValue(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $this->_accountTariffId = $accountTariff->prev_account_tariff_id;

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
        $this->_cache = MttRaw::find()
            ->select([
                'cnt' => 'SUM(chargedqty)',
                'aggr_date' => sprintf("TO_CHAR(connect_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta),
            ])
            ->where([
                'number_service_id' => $accountTariff->prev_account_tariff_id,
                'serviceid' => MttRaw::SERVICE_ID_SMS
            ])
            ->andWhere(['>=', 'connect_time', $dateTimeUtc->format(DATE_ATOM)])
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