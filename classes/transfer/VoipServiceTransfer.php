<?php

namespace app\classes\transfer;

use app\classes\Assert;
use app\classes\Event;
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
    public function process(ClientAccount $targetAccount, $activationDate)
    {
        $targetService = parent::process($targetAccount, $activationDate);

        Event::go('ats2_numbers_check');

        return $targetService;
    }

}