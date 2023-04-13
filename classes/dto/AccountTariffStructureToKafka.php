<?php

namespace app\classes\dto;

use app\classes\adapters\EbcKafka;
use app\classes\Singleton;
use app\classes\Utils;
use app\dao\ClientSuperDao;
use app\modules\uu\dao\AccountTariffStructureGenerator;

class AccountTariffStructureToKafka extends Singleton
{
    const TOPIC = 'stat-event--account-tariff-struct';

    public function anonce($accountTariffId)
    {
        $atStruct = AccountTariffStructureGenerator::me()->getAccountTariffsWithPackages($accountTariffId);

        return EbcKafka::me()->sendMessage(
            self::TOPIC,
            $atStruct,
            (string)$accountTariffId,
            [
                'service_type_id' => $atStruct[0]['service_type']['id'],
                'uuid' => Utils::genUUID(),
            ]
        );
    }
}
