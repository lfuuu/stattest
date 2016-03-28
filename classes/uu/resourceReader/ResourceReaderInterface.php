<?php

namespace app\classes\uu\resourceReader;

use app\classes\uu\model\AccountTariff;
use DateTimeImmutable;

interface ResourceReaderInterface
{
    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime);
}