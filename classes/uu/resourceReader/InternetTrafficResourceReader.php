<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use app\helpers\DateTimeZoneHelper;
use DateTimeImmutable;
use Yii;

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
        $this->createCache($accountTariff->id);
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        if (!isset($this->dateToValue[$date])) {
            Yii::error(sprintf('InternetTrafficResourceReader. Нет данных по ресурсу. AccountTariffId = %d, дата = %s.', $accountTariff->id, $date));
            return null;
        }

        return (int)$this->dateToValue[$date]['in_bytes'] + (int)$this->dateToValue[$date]['out_bytes'];
    }
}