<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsRaw;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\Object;

class VoipPackageCallsResourceReader extends Object implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    private $_callsByPrice = [];
    private $_callsByPricelist = [];

    private $_accountTariffId = null;

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return float|null Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        if ($this->_accountTariffId !== $accountTariff->prev_account_tariff_id) {
            $this->_setDateToValue($accountTariff, $dateTime);
        }

        $cost = 0;
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($this->_callsByPrice[$date]) && !isset($this->_callsByPricelist[$date])) {
            return $cost;
        }

        $tariff = $tariffPeriod->tariff;

        // Цена по направлениям
        $packagePrices = $tariff->packagePrices;
        foreach ($packagePrices as $packagePrice) {
            if (isset($this->_callsByPrice[$date][$packagePrice->id])) {
                $cost += $this->_callsByPrice[$date][$packagePrice->id];
            }
        }

        // Прайслист с МГП
        $packagePricelists = $tariff->packagePricelists;
        foreach ($packagePricelists as $packagePricelist) {
            if (isset($this->_callsByPricelist[$date][$packagePricelist->id])) {
                $cost += $this->_callsByPricelist[$date][$packagePricelist->id];
            }
        }

        return $cost;
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
        $this->_callsByPrice = $this->_callsByPricelist = [];

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
        $query = CallsRaw::find()
            ->select([
                // в CallsRaw стоимость отрицательная, что означает "списание". А в AccountLogResource это должно быть положительным
                'sum_cost' => 'SUM(cost) * -1',
                'nnp_package_price_id',
                'nnp_package_pricelist_id',
                'aggr_date' => sprintf("TO_CHAR(connect_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
            ])
            ->where([
                'account_version' => ClientAccount::VERSION_BILLER_UNIVERSAL,
                'number_service_id' => $accountTariff->prev_account_tariff_id, // основная услуга
            ])
            ->andWhere(['>=', 'connect_time', $dateTimeUtc->format(DATE_ATOM)])
            ->andWhere(['<', 'cost', 0])
            ->groupBy(['aggr_date', 'nnp_package_price_id', 'nnp_package_pricelist_id'])
            ->asArray();

        foreach ($query->each() as $row) {
            $aggrDate = $row['aggr_date'];
            $sumCost = $row['sum_cost'];

            if ($tariffId = $row['nnp_package_price_id']) {
                if (!isset($this->_callsByPrice[$aggrDate])) {
                    $this->_callsByPrice[$aggrDate] = [];
                }

                if (isset($this->_callsByPrice[$aggrDate][$tariffId])) {
                    $this->_callsByPrice[$aggrDate][$tariffId] += $sumCost;
                } else {
                    $this->_callsByPrice[$aggrDate][$tariffId] = $sumCost;
                }

                continue;
            }

            if ($tariffId = $row['nnp_package_pricelist_id']) {
                if (!isset($this->_callsByPricelist[$aggrDate])) {
                    $this->_callsByPricelist[$aggrDate] = [];
                }

                if (isset($this->_callsByPricelist[$aggrDate][$tariffId])) {
                    $this->_callsByPricelist[$aggrDate][$tariffId] += $sumCost;
                } else {
                    $this->_callsByPricelist[$aggrDate][$tariffId] = $sumCost;
                }

                continue;
            }

            Yii::error(
                sprintf(
                    'VoipPackageCallsResourceReader. Звонок по неизвестному пакету. AccountTariffId = %d. date = %s',
                    $accountTariff->prev_account_tariff_id,
                    $dateTime->format(DATE_ATOM)
                )
            );
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