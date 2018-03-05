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
     * @return Amounts
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        $amounts = parent::read($accountTariff, $dateTime, $tariffPeriod);

        if ($amounts->amount !== null) {
            // из b преобразовать в Gb
            $amounts->amount /= 1024 * 1024 * 1024;
        }

        return $amounts;
    }
}