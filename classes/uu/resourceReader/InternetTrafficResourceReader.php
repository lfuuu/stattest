<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\helpers\DateTimeZoneHelper;
use DateTimeImmutable;

class InternetTrafficResourceReader extends CollocationTrafficResourceReader
{
    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $accountTariffId = $accountTariff->getNonUniversalId() ?: $accountTariff->id;
        $this->createCache($accountTariffId);
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        return
            isset($this->dateToValue[$date]) ?
                (
                    (int)$this->dateToValue[$date]['in_bytes'] +
                    (int)$this->dateToValue[$date]['out_bytes']
                )
                :
                null;
    }
}