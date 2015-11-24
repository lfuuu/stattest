<?php

namespace app\models\usages;

use app\models\LogTarif;

interface UsageLogTariffInterface
{

    /**
     * @param string $date
     * @return null|LogTarif
     */
    public function getLogTariff($date = 'now');

}