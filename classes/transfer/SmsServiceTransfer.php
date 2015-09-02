<?php

namespace app\classes\transfer;

/**
 * Класс переноса услуг типа "SMS"
 * @package app\classes\transfer
 */
class SmsServiceTransfer extends ServiceTransfer
{

    public function getTypeTitle()
    {
        return 'SMS';
    }

    public function getTypeDescription()
    {
        return $this->service->tariff ? $this->service->tariff->description : 'Описание';
    }

}