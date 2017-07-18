<?php

namespace app\modules\uu\resourceReader;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;

class VpbxDiskResourceReader extends VpbxResourceReader
{
    protected $fieldName = 'use_space';

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
        // из b преобразовать в Gb
        $value = parent::read($accountTariff, $dateTime, $tariffPeriod);
        return $value === null ? $value : $value / (1024 * 1024 * 1024);
    }
}