<?php

namespace app\classes\uu\converter;

use Yii;

/**
 */
class AccountTariffConverterDummy extends AccountTariffConverterA
{
    /**
     * Доконвертировать тариф
     */
    public function convert($serviceTypeId)
    {
        $this->calcAccountTariffTariff($serviceTypeId);
    }

    /**
     * Создать временную таблицу для конвертации услуги
     */
    protected function createTemporaryTableForAccountTariff()
    {
    }

    /**
     * Конвертировать лог тарифов
     * @return int
     */
    protected function insertIntoAccountTariffLog()
    {
        return 0;
    }
}

