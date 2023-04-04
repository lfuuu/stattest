<?php

namespace app\classes\dto;

use app\classes\adapters\EbcKafka;
use app\classes\Singleton;
use app\dao\ClientSuperDao;
use app\modules\uu\dao\AccountTariffStructureGenerator;

class AccountTariffStructureToKafka extends Singleton
{
    const TOPIC = 'stat-event--account-tariff-struct';

    public function anonce($accountTariffId)
    {
        return EbcKafka::me()->sendMessage(
            self::TOPIC,
            AccountTariffStructureGenerator::me()->getAccountTariffsWithPackages($accountTariffId),
            (string)$accountTariffId
        );
    }
}
