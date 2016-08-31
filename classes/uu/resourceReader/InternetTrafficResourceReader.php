<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
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
        $date = $dateTime->format('Y-m-d');

        return
            isset($this->dateToValue[$date]) ?
                (
                    (int)$this->dateToValue[$date]['in_r'] +
                    (int)$this->dateToValue[$date]['out_r'] +
                    (int)$this->dateToValue[$date]['in_r2'] +
                    (int)$this->dateToValue[$date]['out_r2'] +
                    (int)$this->dateToValue[$date]['in_f'] +
                    (int)$this->dateToValue[$date]['out_f']
                )
                :
                null;
    }
}