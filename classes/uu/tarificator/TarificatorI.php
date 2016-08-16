<?php

namespace app\classes\uu\tarificator;

/**
 */
interface TarificatorI
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    public function tarificate($accountTariffId = null);
}
