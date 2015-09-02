<?php

namespace app\classes\transfer;

/**
 * Класс переноса услуги "Welltime"
 * @package app\classes\transfer
 */
class WelltimeServiceTransfer extends ServiceTransfer
{

    public function getTypeTitle()
    {
        return 'Welltime';
    }

    public function getTypeDescription()
    {
        return $this->service->tariff ? $this->service->tariff->description : 'Описание';
    }

}