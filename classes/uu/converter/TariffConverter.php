<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\ServiceType;
use BadMethodCallException;
use Yii;

/**
 */
class TariffConverter
{
    protected $serviceTypeIdToConverter = [
        ServiceType::ID_VPBX => '\app\classes\uu\converter\TariffConverterVpbx',

        ServiceType::ID_VOIP => '\app\classes\uu\converter\TariffConverterVoip',
        ServiceType::ID_VOIP_PACKAGE => '\app\classes\uu\converter\TariffConverterVoipPackage',

        ServiceType::ID_INTERNET => '\app\classes\uu\converter\TariffConverterInternet',
        ServiceType::ID_COLLOCATION => '\app\classes\uu\converter\TariffConverterCollocation',

        ServiceType::ID_VPN => '\app\classes\uu\converter\TariffConverterVpn',

        ServiceType::ID_IT_PARK => '\app\classes\uu\converter\TariffConverterExtra',
        ServiceType::ID_DOMAIN => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_MAILSERVER => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_ATS => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_SITE => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_USPD => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_WELLSYSTEM => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_WELLTIME_PRODUCT => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_EXTRA => '\app\classes\uu\converter\TariffConverterDummy',
        ServiceType::ID_SMS_GATE => '\app\classes\uu\converter\TariffConverterDummy',

        ServiceType::ID_SMS => '\app\classes\uu\converter\TariffConverterSms',

        ServiceType::ID_WELLTIME_SAAS => '\app\classes\uu\converter\TariffConverterWelltimeSaas',

        ServiceType::ID_CALL_CHAT => '\app\classes\uu\converter\TariffConverterCallChat',
    ];

    /**
     * Доконвертировать тариф определенного типа
     * @param int $serviceTypeId
     */
    public function convertByServiceTypeId($serviceTypeId)
    {
        if (!isset($this->serviceTypeIdToConverter[$serviceTypeId])) {
            throw new BadMethodCallException('Not implemented for serviceTypeId = ' . $serviceTypeId);
        }

        /** @var TariffConverterA $tariffConverter */
        $converterClassName = $this->serviceTypeIdToConverter[$serviceTypeId];
        $tariffConverter = new $converterClassName();
        $tariffConverter->convert($serviceTypeId);
    }
}
