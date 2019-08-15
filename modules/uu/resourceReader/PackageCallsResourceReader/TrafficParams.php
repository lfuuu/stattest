<?php

namespace app\modules\uu\resourceReader\PackageCallsResourceReader;

use app\modules\uu\models\AccountTariff;

/**
 * Параметры и обработчик результа для выборки по траффику (из callsraw) универсальной услуги
 * @package app\modules\uu\resourceReader\PackageCallsResourceReader
 */
class TrafficParams
{
    public $accountTariff;

    public $clientAccountId;
    public $prevAccountTariffId;

    public $resultFields = [];

    public function __construct(AccountTariff $accountTariff)
    {
        $this->setAccountTariff($accountTariff);
    }

    /**
     * Установить универсальную услугу
     *
     * @param AccountTariff $accountTariff
     */
    public function setAccountTariff(AccountTariff $accountTariff)
    {
        $this->accountTariff = $accountTariff;

        $this->clientAccountId = $accountTariff->client_account_id;
        $this->prevAccountTariffId = $accountTariff->prev_account_tariff_id;

        $this->resultFields = [];
    }

    /**
     * Обработать результат выборки
     *
     * @param array $row
     * @return array
     */
    public function updateResult(array $row)
    {
        if ($this->resultFields) {
            $row = array_merge($row, $this->resultFields);
        }

        return $row;
    }
}