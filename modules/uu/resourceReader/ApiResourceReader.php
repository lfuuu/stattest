<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\ApiRaw;
use app\models\billing\CallsRaw;
use app\models\billing\DataRaw;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\BaseObject;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class ApiResourceReader
 * @package app\modules\uu\resourceReader
 */
class ApiResourceReader extends BaseObject implements ResourceReaderInterface
{
    private $accountTariffId = null;

    private $data = [];
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
        if ($this->accountTariffId !== $accountTariff->id) {
            $this->setDateToValue($accountTariff, $dateTime);
        }

        $dateStr = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        $price = isset($this->data[$dateStr]) ? $this->data[$dateStr] : null;

        if (!$price) {
            // нет данных
            return new Amounts(0, 0);
        }


        return new Amounts(abs($price), 0);
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


        $query =
            ApiRaw::find()
                ->alias('ar')
                ->joinWith('accountTariffLight al', true, 'INNER JOIN')
                ->select([
                    'price' => 'SUM(-cost)', // стоимость звонка для клиента. Сделаем ее положительной
                    'date' => sprintf("TO_CHAR(connect_time + INTERVAL '%d hours', 'YYYY-MM-DD')", $hoursDelta)
                ])
                ->where([
                    'ar.service_api_id' => $accountTariff->prev_account_tariff_id,
                    'al.account_package_id' => $this->accountTariffId,
                ])
                ->andWhere(['>=', 'ar.connect_time', $dateTimeUtc->format(DateTimeZoneHelper::DATETIME_FORMAT)])
                ->groupBy('date')
                ->indexBy('date')
                ->orderBy(['date' => SORT_ASC]);


        $this->data = $query->asArray()->column();
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