<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\DataRaw;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\BaseObject;
use yii\db\Expression;

/**
 * Class InternetResourceReader
 * @package app\modules\uu\resourceReader
 *
 * @property bool $isMonthPricePerUnit
 */
class InternetResourceReader extends BaseObject implements ResourceReaderInterface
{
    protected $data = [];

    protected $accountTariffId = null;
    protected $minDateTime = null;


    /**
     * Вернуть количество потребленного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return Amounts
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        // сменилась основная услуга у пакета, или дата получаемых данных раньше, чем сохраннено в кеше
        if ($this->accountTariffId !== $accountTariff->prev_account_tariff_id || ($this->minDateTime && ($dateTime < $this->minDateTime))) {
            echo($this->accountTariffId !== $accountTariff->prev_account_tariff_id ? 'Z ' : 'DateChangedSame ');
            $this->setDateToValue($accountTariff, $dateTime);
        }

        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        return new Amounts($this->data[$date][$accountTariff->id][$tariffPeriod->tariff_id] ?? 0, 0);
    }

    protected function setDateToValue(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $this->accountTariffId = $accountTariff->prev_account_tariff_id;
        $this->minDateTime = $dateTime;
        $this->data = [];

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

        // ресурсы ограничиваем концом сегоднящнего дня
        $maxDateTime = (new DateTimeImmutable('now'))->setTime(0, 0, 0)->modify('+ 1 day');

        $dataQuery = DataRaw::find()
            ->alias('d')
            ->joinWith('accountTariffLight l', true, 'INNER JOIN')
            ->select([
                'account_package_id' => 'l.account_package_id',
                'tariff_id' => 'l.tariff_id',
                'price' => new Expression('round(-sum(cost)::numeric, 2)'),
                'aggr_date' => sprintf("TO_CHAR(d.charge_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
            ])
            ->where([
                'account_id' => $accountTariff->client_account_id,
                'number_service_id' => $accountTariff->prev_account_tariff_id,
            ])
            ->andWhere(['>=', 'charge_time', $dateTimeUtc->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->andWhere(['<', 'charge_time', $maxDateTime->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->groupBy(['account_package_id', 'aggr_date', 'tariff_id'])
            ->orderBy(['aggr_date' => SORT_ASC, 'account_package_id'  => SORT_ASC, 'tariff_id' => SORT_ASC]);

        $data = $dataQuery->createCommand(DataRaw::getDb())->queryAll();
            foreach ($data as $row) {
                $date = $row['aggr_date'];;
                $accountPackageId = $row['account_package_id'];
                $tariffId = $row['tariff_id'];

                if (!isset($this->data[$date])) {
                    $this->data[$date] = [];
                }

                if (!isset($this->data[$date][$accountPackageId])) {
                    $this->data[$date][$accountPackageId] = [];
                }

                $this->data[$date][$accountPackageId][$tariffId] = $row['price'];
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