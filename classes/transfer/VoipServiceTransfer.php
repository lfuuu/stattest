<?php

namespace app\classes\transfer;
use app\models\ClientAccount;

/**
 * Класс переноса услуг типа "Телефония номера"
 * @package app\classes\transfer
 */
class VoipServiceTransfer extends ServiceTransfer
{

    public function process(ClientAccount $targetAccount)
    {
        //$service = parent::process($targetAccount);

        //$this->service->voip_number->
    }

}