<?php

namespace app\classes\uu\converter;

use app\classes\uu\model\ServiceType;
use BadMethodCallException;
use Yii;

/**
 */
class AccountTariffConverter
{
    protected $serviceTypeIdToConverter = [
        ServiceType::ID_VPBX => '\app\classes\uu\converter\AccountTariffConverterVpbx',

        ServiceType::ID_VOIP => '\app\classes\uu\converter\AccountTariffConverterVoip',
        ServiceType::ID_VOIP_PACKAGE => '\app\classes\uu\converter\AccountTariffConverterVoipPackage',

        ServiceType::ID_INTERNET => '\app\classes\uu\converter\AccountTariffConverterInternet',
        ServiceType::ID_COLLOCATION => '\app\classes\uu\converter\AccountTariffConverterDummy',

        ServiceType::ID_VPN => '\app\classes\uu\converter\AccountTariffConverterDummy',

        ServiceType::ID_IT_PARK => '\app\classes\uu\converter\AccountTariffConverterExtra',
        ServiceType::ID_DOMAIN => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_MAILSERVER => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_ATS => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_SITE => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_USPD => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_WELLSYSTEM => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_WELLTIME_PRODUCT => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_EXTRA => '\app\classes\uu\converter\AccountTariffConverterDummy',
        ServiceType::ID_SMS_GATE => '\app\classes\uu\converter\AccountTariffConverterDummy',

        ServiceType::ID_SMS => '\app\classes\uu\converter\AccountTariffConverterSms',

        ServiceType::ID_WELLTIME_SAAS => '\app\classes\uu\converter\AccountTariffConverterWelltimeSaas',

        ServiceType::ID_CALL_CHAT => '\app\classes\uu\converter\AccountTariffConverterCallChat',
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

        echo PHP_EOL . 'ServiceTypeId = ' . $serviceTypeId;

        /** @var AccountTariffConverterA $tariffConverter */
        $converterClassName = $this->serviceTypeIdToConverter[$serviceTypeId];
        $tariffConverter = new $converterClassName();
        $tariffConverter->convert($serviceTypeId);
    }
}
