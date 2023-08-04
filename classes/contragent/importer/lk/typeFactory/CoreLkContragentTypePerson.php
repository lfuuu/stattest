<?php

namespace app\classes\contragent\importer\lk\typeFactory;

use app\models\ClientContragent;
use app\models\ClientContragentPerson;

class CoreLkContragentTypePerson extends CoreLkContragentTypeDefault
{
    public static $orgType = self::ORG_TYPE_PHYSICAL;

    public function getStatLegalType()
    {
        return ClientContragent::PERSON_TYPE;
    }

    protected function makeStatModel()
    {
        $resp = $this->coreLkContragent->getDataResponse();

        if (!parent::makeStatModel()) {
            return true;
        }

        $contragent = $this->contragent;
        $person = new ClientContragentPerson();

        $person->last_name = ClientContragentPerson::normalizeName($resp['lastName'] ?? null);
        $person->first_name = ClientContragentPerson::normalizeName($resp['firstName'] ?? null);
        $person->middle_name = ClientContragentPerson::normalizeName($resp['middleName'] ?? null);
        $person->birthday = $this->helper_date($resp['birthDate'] ?? $resp['birthday'] ?? null);

        $person->registration_address = $resp['addr']['fullAddressStr'] ?? $resp['addr']['addressStr'] ?? null;

        $person->passport_number = $resp['document']['number'] ?? null;
        $person->passport_serial = $resp['document']['series'] ?? null;
        $person->passport_date_issued = $this->helper_date($resp['document']['issueDate'] ?? null);
        $person->passport_issued = $resp['document']['issuedBy'] ?? null;
        $person->birthplace = $resp['document']['birthplace'] ?? null;

        $contragent->legal_type = ClientContragent::PERSON_TYPE;
        $contragent->name = $contragent->name_full = $person->getFullName();

        $contragent->populateRelation('personModel', $person);

        return true;
    }
}
