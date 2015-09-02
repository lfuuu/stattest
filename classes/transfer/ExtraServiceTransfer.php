<?php

namespace app\classes\transfer;

/**
 * Класс переноса услуг типа "Дополнительные услуги"
 * @package app\classes\transfer
 */
class ExtraServiceTransfer extends ServiceTransfer
{

    public function getTypeTitle()
    {
        return 'Доп. услуги';
    }

    public function getTypeDescription()
    {
        return $this->service->tariff ? $this->service->tariff->description : 'Описание';
    }

}