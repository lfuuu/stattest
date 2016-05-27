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
        $country_id = Country::RUSSIA;
    public $last_name;
    public $first_name;
    public $middle_name;
    public $passport_serial;
    public $passport_number;
    public $passport_issued;
    public $passport_date_issued;
    public $address;

    public function rules()
    {
        $rules = [];

        $rules[] = [['legal_type'], 'required'];
        $rules[] = [['middle_name'], 'safe'];

        $rules[] = [
            ["name", "inn", "address_jur", "position", "fio"],
            "required",
            'when' => function ($model) {
                return $model->legal_type == 'legal';
            }
        ];

        $rules[] = [
            ['first_name', 'last_name', "address"],
            "required",
            'when' => function ($model) {
                return $model->legal_type == 'person';
            }
        ];

        $rules[] = [
            ["kpp"],
            "required",
            'when' => function ($model) {
                return $model->legal_type == 'legal';
            }
        ];

        $rules[] = [
            [
                'name', /*'address_jur',*/
                'first_name',
                'last_name',
                'inn',
                'ogrn',
                'address'
            ],
            'required',
            'when' => function ($model) {
                return $model->legal_type == 'ip';
            }
        ];

        $rules[] = [
            [
                'passport_serial',
                'passport_number',
                'passport_date_issued',
                'passport_issued'
            ],
            'required',
            'when' => function ($model) {
                return $model->legal_type == 'person';
            }
        ];

        return $rules;
    }

    public function saveInContragent(ClientAccount $account)
    {
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


        if ($contragent->legal_type == "legal") {
            if (trim($contragent->name_full) == "") {
                $contragent->name_full = $contragent->name;
            }
        }

        if ($contragent->legal_type == "person") {
            $contragent->name = $contragent->name_full = $this->last_name . " " . $this->first_name . ($this->middle_name ? " " . $this->middle_name : "");
        }

        $contragent->save(false);

        $contract = ClientContract::findOne($account->contract->id);

        if ($contragent->legal_type == "ip" || $contragent->legal_type == "person") {
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
