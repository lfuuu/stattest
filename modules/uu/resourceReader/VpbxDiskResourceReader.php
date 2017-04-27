<?php

namespace app\modules\uu\resourceReader;

use app\modules\uu\models\AccountTariff;
use DateTimeImmutable;

class VpbxDiskResourceReader extends VpbxResourceReader
{
    protected $fieldName = 'use_space';

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        // из b преобразовать в Gb
        $value = parent::read($accountTariff, $dateTime);
        return $value === null ? $value : $value / (1024 * 1024 * 1024);
    }
}