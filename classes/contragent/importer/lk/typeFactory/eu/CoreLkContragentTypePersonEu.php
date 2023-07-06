<?php

namespace app\classes\contragent\importer\lk\typeFactory\eu;

use app\classes\contragent\importer\lk\typeFactory\CoreLkContragentTypeDefault;
use app\models\ClientContragent;
use app\models\ClientContragentPerson;

class CoreLkContragentTypePersonEu extends CoreLkContragentTypeDefault
{
    public static $orgType = self::ORG_TYPE_PHYSICAL;

    public function getStatLegalType()
    {
        return ClientContragent::PERSON_TYPE;
    }

    protected function makeStatModel()
    {
        $row = $this->coreLkContragent->row;

        if (!parent::makeStatModel()) {
            return true;
        }

        $contragent = $this->contragent;
        $contragent->legal_type = ClientContragent::PERSON_TYPE;

        $person = new ClientContragentPerson();

        if ($row['lang_code']) {
            $contragent->lang_code = $row['lang_code'];
        }

        if ($row['country_id']) {
            $contragent->country_id = $row['country_id'];
        }

        if ($row['name']) {
            $contragent->name = $contragent->name_full = $row['name'];

            if (preg_match('/\s*(\S+)\s+(.*)\s*/', $contragent->name, $matches)) {
                $person->last_name = $matches[1];
                $person->first_name = $matches[2];
            } else {
                $person->last_name = $contragent->name;
                $person->first_name = $person->middle_name = '';
            }
        }

        $data = $this->coreLkContragent->getDataResponse();
        $birthday = $data['birthday'] ?? null;
        if ($birthday) {
            $person->birthday = $this->helper_date($birthday);
        }

        if ($row['address']) {
            $contragent->address_jur = $person->registration_address = $row['address'].'aaa';
        }

        $contragent->populateRelation('personModel', $person);

        return true;
    }
}
