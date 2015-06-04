<?php

namespace app\classes\transfer;

use app\models\ClientAccount;

/**
 * Класс переноса услуг типа "Телефония номера"
 * @package app\classes\transfer
 */
class VoipServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process(ClientAccount $targetAccount)
    {
        $targetService = parent::process($targetAccount);

        Event::go('ats2_numbers_check');

        return $targetService;
    }

}