<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\NnpLog;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\Object;

class NmpNumberResourceReader extends Object implements ResourceReaderInterface
{
    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        // в БД хранится в UTC, но считать надо в зависимости от таймзоны клиента
        $dateTimeUtc = $dateTime->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        return NnpLog::find()
            ->where(['account_tariff_id' => $accountTariff->id])
            ->andWhere([
                'BETWEEN',
                'insert_time',
                $dateTimeUtc->format(DateTimeZoneHelper::DATETIME_FORMAT),
                $dateTimeUtc->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ])
            ->count();
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
        return true;
    }
}