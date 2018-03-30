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
use yii\db\Query;

abstract class PackageCallsResourceReader extends Object implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    private $_callsByPrice = []; // [$date => [$packagePriceId => [0 => $price, 1 => $costPrice]]]
    private $_callsByPricelist = []; // [$date => [$packagePricelistId => [0 => $price, 1 => $costPrice]]]

    private $_accountTariffId = null;

    private static $_packages = [];

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

        $price = $costPrice = 0;
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($this->_callsByPrice[$date]) && !isset($this->_callsByPricelist[$date])) {
            return new Amounts($price, $costPrice);
        }

        $tariffId = $tariffPeriod->tariff_id;
        if (!isset(self::$_packages[$tariffId])) {
            // записать в кэш
            $tariff = $tariffPeriod->tariff;
            self::$_packages[$tariffId] = [
                'packagePriceIds' => $tariff->getPackagePrices()->select(['id'])->column(),
                'packagePricelistIds' => $tariff->getPackagePricelists()->select(['id'])->column(),
            ];
        }

        $package = self::$_packages[$tariffId];

        // Цена по направлениям
        foreach ($package['packagePriceIds'] as $packagePriceId) {
            if (isset($this->_callsByPrice[$date][$packagePriceId])) {
                $price += $this->_callsByPrice[$date][$packagePriceId][0];
                $costPrice += $this->_callsByPrice[$date][$packagePriceId][1];
            }
        }

        // Прайслист с МГП
        foreach ($package['packagePricelistIds'] as $packagePricelistId) {
            if (isset($this->_callsByPricelist[$date][$packagePricelistId])) {
                $price += $this->_callsByPricelist[$date][$packagePricelistId][0];
                $costPrice += $this->_callsByPricelist[$date][$packagePricelistId][1];
            }
        }

        return new Amounts($price, $costPrice);
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


        $connectTime = $dateTimeUtc->format(DATE_ATOM);

        // этот метод вызывается в цикле по услуге, внутри в цикле по возрастанию даты.
        // Поэтому надо кэшировать по одной услуге все даты в будущем, сгруппированные до суткам в таймзоне клиента
        $query = CallsRaw::find()
            ->select([
                'sum_price' => 'SUM(-calls_price.cost)', // стоимость звонка для клиента. Сделаем ее положительной
                'sum_cost_price' => 'SUM(COALESCE(calls_cost_price.cost, 0))', // себестоимость
                'nnp_package_price_id' => 'calls_price.nnp_package_price_id',
                'nnp_package_pricelist_id' => 'calls_price.nnp_package_pricelist_id',
                'aggr_date' => sprintf("TO_CHAR(calls_price.connect_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
            ])
            ->from(CallsRaw::tableName() . ' calls_price')// чтобы назначить алиас.
            ->leftJoin(CallsRaw::tableName() . ' calls_cost_price',
                'calls_price.peer_id = calls_cost_price.id AND calls_cost_price.connect_time >= :connectTime', [':connectTime' => $connectTime])// join себя же по peer_id. Чтобы узнать себестоимость в другом плече (терминации)
            ->where([
                'calls_price.account_version' => ClientAccount::VERSION_BILLER_UNIVERSAL,
            ])
            ->andWhere(['>=', 'calls_price.connect_time', $connectTime])
            ->andWhere(['<', 'calls_price.cost', 0])
            ->groupBy(['aggr_date', 'calls_price.nnp_package_price_id', 'calls_price.nnp_package_pricelist_id'])
            ->asArray();

        $this->andWhere($query, $accountTariff);

        foreach ($query->each() as $row) {
            $aggrDate = $row['aggr_date'];
            $sumPrice = $row['sum_price'];
            $sumCostPrice = $row['sum_cost_price'];

            if ($tariffId = $row['nnp_package_price_id']) {
                if (!isset($this->_callsByPrice[$aggrDate])) {
                    $this->_callsByPrice[$aggrDate] = [];
                }

                if (isset($this->_callsByPrice[$aggrDate][$tariffId])) {
                    $this->_callsByPrice[$aggrDate][$tariffId][0] += $sumPrice;
                    $this->_callsByPrice[$aggrDate][$tariffId][1] += $sumCostPrice;
                } else {
                    $this->_callsByPrice[$aggrDate][$tariffId] = [$sumPrice, $sumCostPrice];
                }

                continue;
            }

            if ($tariffId = $row['nnp_package_pricelist_id']) {
                if (!isset($this->_callsByPricelist[$aggrDate])) {
                    $this->_callsByPricelist[$aggrDate] = [];
                }

                if (isset($this->_callsByPricelist[$aggrDate][$tariffId])) {
                    $this->_callsByPricelist[$aggrDate][$tariffId][0] += $sumPrice;
                    $this->_callsByPricelist[$aggrDate][$tariffId][1] += $sumCostPrice;
                } else {
                    $this->_callsByPricelist[$aggrDate][$tariffId] = [$sumPrice, $sumCostPrice];
                }

                continue;
            }

            Yii::error(
                sprintf(
                    'PackageCallsResourceReader. Звонок по неизвестному пакету. AccountTariffId = %d. date = %s',
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

    /**
     * @param Query $query
     * @param AccountTariff $accountTariff
     */
    abstract protected function andWhere(Query $query, AccountTariff $accountTariff);
}