<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsRaw;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\resourceReader\PackageCallsResourceReader\TrafficParamsManager;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\BaseObject;
use yii\db\Query;

abstract class PackageCallsResourceReader extends BaseObject implements ResourceReaderInterface
{
    /** @var [] кэш данных */
    protected $callsByPrice = []; // [$date => [$packagePriceId => [0 => $price, 1 => $costPrice]]]
    protected $callsByPriceList = []; // [$date => [$packagePricelistId => [0 => $price, 1 => $costPrice]]]

    protected $accountTariffId = null;
    protected $minDateTime = null;

    protected static $packages = [];

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return Amounts
     * @throws \yii\db\Exception
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        // сменилась основная услуга у пакета, или дата получаемых данных раньше, чем сохраннено в кеше
        if ($this->accountTariffId !== $accountTariff->prev_account_tariff_id || ($this->minDateTime && ($dateTime < $this->minDateTime))) {
            echo ($this->accountTariffId !== $accountTariff->prev_account_tariff_id ? 'Z ' : 'DateChangedSame ');
            $this->setDateToValue($accountTariff, $dateTime);
        }

        $price = $costPrice = 0;
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($this->callsByPrice[$date]) && !isset($this->callsByPriceList[$date])) {
            return new Amounts($price, $costPrice);
        }

        $tariffId = $tariffPeriod->tariff_id;
        if (!isset(self::$packages[$tariffId])) {
            // записать в кэш
            $tariff = $tariffPeriod->tariff;
            self::$packages[$tariffId] = [
                'packagePriceIds' => $tariff->getPackagePrices()->select(['id'])->column(),
                'packagePricelistIds' => $tariff->getPackagePricelists()->select(['id'])->column(),
                'packagePricelistNnpIds' => $tariff->getPackagePricelistsNnp()->select(['id'])->column(),
            ];
        }

        $package = self::$packages[$tariffId];

        // Цена по направлениям
        foreach ($package['packagePriceIds'] as $packagePriceId) {
            if (isset($this->callsByPrice[$date][$packagePriceId])) {
                $price += $this->callsByPrice[$date][$packagePriceId][0];
                $costPrice += $this->callsByPrice[$date][$packagePriceId][1];
            }
        }

        // Прайслист с МГП
        foreach ($package['packagePricelistIds'] as $packagePricelistId) {
            if (isset($this->callsByPriceList[$date][$packagePricelistId])) {
                $price += $this->callsByPriceList[$date][$packagePricelistId][0];
                $costPrice += $this->callsByPriceList[$date][$packagePricelistId][1];
            }
        }

        // Прайслист v2
        foreach ($package['packagePricelistNnpIds'] as $packagePricelistId) {
            if (isset($this->callsByPriceList[$date][$packagePricelistId])) {
                $price += $this->callsByPriceList[$date][$packagePricelistId][0];
                $costPrice += $this->callsByPriceList[$date][$packagePricelistId][1];
            }
        }

        return new Amounts($price, $costPrice);
    }

    /**
     * Построить кэш по этой услуге
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    protected function setDateToValue(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $this->accountTariffId = $accountTariff->prev_account_tariff_id;
        $this->minDateTime = $dateTime;

        $this->callsByPrice = $this->callsByPriceList = [];

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

        // ресурсы ограничиваем концом сегоднящнего дня
        $maxDateTime = (new DateTimeImmutable('now'))->setTime(0, 0, 0)->modify('+ 1 day')->format(DATE_ATOM);

        CallsRaw::setPgTimeout(CallsRaw::PG_CALCULATE_RESOURCE_TIMEOUT);

        // этот метод вызывается в цикле по услуге, внутри в цикле по возрастанию даты.
        // Поэтому надо кэшировать по одной услуге все даты в будущем, сгруппированные до суткам в таймзоне клиента

        $isAggr = false;

        $query = CallsRaw::find()
            ->select([
                'sum_price' => 'SUM(-calls_price.cost)', // стоимость звонка для клиента. Сделаем ее положительной
                'sum_cost_price' => 'SUM(COALESCE(margin, 0))', // себестоимость
                'nnp_package_price_id' => 'calls_price.nnp_package_price_id',
                'nnp_package_pricelist_id' => 'calls_price.nnp_package_pricelist_id',
                'aggr_date' => sprintf("TO_CHAR(calls_price.connect_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
            ])
            ->from(($isAggr ? 'calls_aggr.uu_aggr' : CallsRaw::tableName()) . ' calls_price')// чтобы назначить алиас.
            ->where([
                'calls_price.account_version' => ClientAccount::VERSION_BILLER_UNIVERSAL,
            ])
            ->andWhere(['>=', 'calls_price.connect_time', $connectTime])
            ->andWhere(['<', 'calls_price.connect_time', $maxDateTime])
            ->groupBy([
                'aggr_date',
                'calls_price.nnp_package_price_id',
                'calls_price.nnp_package_pricelist_id']
            )
            ->orderBy(['aggr_date' => SORT_ASC])
            ->asArray();

        if (!$isAggr) {
            $query->leftJoin('calls_margin.margin m', 'calls_price.mcn_callid = m.mcn_callid and calls_price.id = m.call_id');
        }

        $this->andWhere($query, $accountTariff);

        $trafficParams = TrafficParamsManager::me()->getTrafficParams($accountTariff);

        foreach ($query->each() as $row) {
            $aggrDate = $row['aggr_date'];
            $sumPrice = $row['sum_price'];
            $sumCostPrice = $row['sum_cost_price'];

            $row = $trafficParams->updateResult($row);
            if ($tariffId = $row['nnp_package_price_id']) {
                if (!isset($this->callsByPrice[$aggrDate])) {
                    $this->callsByPrice[$aggrDate] = [];
                }

                if (isset($this->callsByPrice[$aggrDate][$tariffId])) {
                    $this->callsByPrice[$aggrDate][$tariffId][0] += $sumPrice;
                    $this->callsByPrice[$aggrDate][$tariffId][1] += $sumCostPrice;
                } else {
                    $this->callsByPrice[$aggrDate][$tariffId] = [$sumPrice, $sumCostPrice];
                }

                continue;
            }

            if ($tariffId = $row['nnp_package_pricelist_id']) {
                if (!isset($this->callsByPriceList[$aggrDate])) {
                    $this->callsByPriceList[$aggrDate] = [];
                }

                if (isset($this->callsByPriceList[$aggrDate][$tariffId])) {
                    $this->callsByPriceList[$aggrDate][$tariffId][0] += $sumPrice;
                    $this->callsByPriceList[$aggrDate][$tariffId][1] += $sumCostPrice;
                } else {
                    $this->callsByPriceList[$aggrDate][$tariffId] = [$sumPrice, $sumCostPrice];
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