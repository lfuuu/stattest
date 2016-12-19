<?php

namespace app\forms\lk_wizard;

use app\classes\Form;

use app\models\ClientContragent;
use app\models\ClientContragentPerson;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Country;
use app\models\Organization;

class WizardContragentMcnForm extends Form
{
    public $legal_type,
        $name,
        $name_full,
        $address_jur,
        $inn = "",
        $kpp = "",
        $position = "",
        $fio = "",
        $tax_regime = ClientContragent::TAX_REGTIME_OCH_VAT18,
        $opf_id = 0,
        $okpo = "",
        $okvd = "",
        $ogrn = "",
        $country_id = Country::RUSSIA,
        $last_name,
        $first_name,
        $middle_name,
        $passport_serial,
        $passport_number,
        $passport_issued,
        $passport_date_issued,
        $birthday,
        $address
    ;

    public function rules()
    {
        $rules = [];

        $rules[] = [['legal_type'], 'required'];
        $rules[] = [['middle_name'], 'safe'];

        $rules[] = [
            ["name", "inn", "kpp", "address_jur", "position", "fio", "tax_regime"],
            "required",
            'when' => function ($model) {
                return $model->legal_type == ClientContragent::LEGAL_TYPE;
            }
        ];

        $rules[] = [
            "tax_regime",
            "in",
            "range" => array_keys(ClientContragent::$taxRegtimeTypes),
            "when" => function ($model) {
                return $model->legal_type == ClientContragent::LEGAL_TYPE;
            }
        ];

        $rules[] = [
            [
                'first_name',
                'last_name',
                'address',
                'birthday',
                'passport_serial',
                'passport_number',
                'passport_date_issued',
                'passport_issued'
            ],
            "required",
            'when' => function ($model) {
                return $model->legal_type == ClientContragent::PERSON_TYPE;
            }
        ];

        $rules[] = [
            [
                'name',
                'address_jur',
                'inn',
                'ogrn',
            ],
            'required',
            'when' => function ($model) {
                return $model->legal_type == ClientContragent::IP_TYPE;
            }
        ];

        return $rules;
    }

    public function saveInContragent(ClientAccount $account)
    {
        /** @var ClientContragent $contragent */
        $contragent = $account->contragent;
        $contragent->legal_type = $this->legal_type;
        $contragent->name = $this->name;
        $contragent->name_full = $this->name_full;
        $contragent->address_jur = $this->address_jur;
        $contragent->inn = $this->inn;
        $contragent->kpp = $this->kpp;
        $contragent->ogrn = $this->ogrn;
        $contragent->position = $this->position;
        $contragent->fio = $this->fio;


        if ($contragent->legal_type == ClientContragent::LEGAL_TYPE) {
            if (trim($contragent->name_full) == "") {
                $contragent->name_full = $contragent->name;
            }
            $contragent->tax_regime = $this->tax_regime;
        }

        if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $contragent->name = $contragent->name_full = $this->last_name . " " . $this->first_name . ($this->middle_name ? " " . $this->middle_name : "");
        }

        $contragent->save(false);

        $contract = ClientContract::findOne($account->contract->id);

        if ($contragent->legal_type == ClientContragent::IP_TYPE || $contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $person = $contragent->person;
            if (!$person) {
                $person = new ClientContragentPerson();
                $person->contragent_id = $contragent->id;
            }

            $person->first_name = $this->first_name;
            $person->last_name = $this->last_name;
            $person->middle_name = $this->middle_name;
            $person->registration_address = $this->address;
            $person->passport_serial = $this->passport_serial;
            $person->passport_number = $this->passport_number;
            $person->passport_issued = $this->passport_issued;
            $person->passport_date_issued = $this->passport_date_issued;
            $person->birthday = $this->birthday;

            $person->save();
            $contragent->refresh();

            if ($contract->organization_id != Organization::MCM_TELEKOM) {
                $contract->organization_id = Organization::MCM_TELEKOM;
                $contract->save();
            }
        } else { //legal
            if ($contract->organization_id != Organization::MCN_TELEKOM) {
                $contract->organization_id = Organization::MCN_TELEKOM;
                $contract->save();
            }
        }

        return true;
    }
}
