<?php

namespace app\classes\uu\converter;

use Yii;

/**
 */
class TariffConverterDummy extends TariffConverterA
{
    /**
     * Доконвертировать тариф
     */
    public function convert()
    {
    }

    /**
     * Создать временную таблицу для конвертации тарифа
     */
    protected function createTemporaryTableForTariff()
    {
    }

    /**
     * Создать временную таблицу для конвертации периодов тарифа
     */
    protected function createTemporaryTableForTariffPeriod()
    {
    }
}

