<?php

namespace app\classes\contragent\importer\lk\typeFactory;

use app\models\ClientContragent;
use app\models\ClientContragentPerson;

class CoreLkContragentTypeIp extends CoreLkContragentTypeDefault
{
    public static $orgType = self::ORG_TYPE_INDIVIDUAL;

    public function getStatLegalType()
    {
        return ClientContragent::IP_TYPE;
    }

    protected function makeStatModel()
    {
        $lkContragent = $this->coreLkContragent;
        $resp = $lkContragent->getDataResponse();

        if (!parent::makeStatModel()) {
            return true;
        }

        $contragent = $this->contragent;

        $contragent->legal_type = ClientContragent::IP_TYPE;
        $contragent->name = $resp['value'] ?? $resp['unrestricted_value'] ?? $resp['data']['name']['short_with_opf'] ?? $lkContragent->getName();
//        $contragent->name_full = $resp['data']['name']['full_with_opf'] ?? $contragent->name;
        $contragent->name_full = $resp['data']['name']['short_with_opf'] ?? $contragent->name;

        $contragent->inn = $resp['data']['inn'] ?? null;
        $contragent->ogrn = $resp['data']['ogrn'] ?? null;
        $contragent->okpo = $resp['data']['okpo'] ?? null;
//        $contragent->address_jur = $resp['data']['address']['unrestricted_value'] ?? null;
        $address = $lkContragent->getAddressRegistratonIp();
        if ($address) {
            $contragent->address_jur = $address;
            $contragent->address_registration_ip = $address;
        }
//        $contragent->opf_id = CodeOpf::IP; // @TODO

        $taxRegime = $lkContragent->getTaxRegime();
        if ($taxRegime) {
            $contragent->tax_regime = $taxRegime;
        }

        $m = preg_split('/\s+/', trim($contragent->name));

        $person = new ClientContragentPerson();

        if ($m && is_array($m) && $m[0] == 'ИП') {
            $person->last_name = $m[1] ?? null;
            $person->first_name = $m[2] ?? null;
            $person->middle_name = $m[3] ?? null;
        }

//        $person->registration_address = $contragent->address_jur;

        $contragent->populateRelation('personModel', $person);

        return true;
    }
}
