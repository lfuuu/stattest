<?php
namespace app\forms\contragent;

use app\forms\contragent\ContragentForm;
use app\models\ClientContragentPerson;
use app\models\ClientContragent;

class ContragentEditForm extends ContragentForm
{
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['name', 'address_jur', 'inn', 'kpp', 'position', 'fio'], 'required',
            'when' => function($model){ return $model->legal_type=='legal';}
        ];

        $rules[] = [['name', 'address_jur', 'first_name', 'last_name', 'inn', 'ogrn'], 'required',
            'when' => function($model){ return $model->legal_type=='ip';}
        ];

        $rules[] = [['first_name', 'last_name', 'middle_name', 'passport_serial', 'passport_number', 'passport_date_issued', 'passport_issued', 'address'], 'required',
            'when' => function($model){ return $model->legal_type=='person';} 
        ];

        return $rules;
    }

    public function saveInContragent(ClientContragent $contragent)
    {
        $contragent->legal_type = $this->legal_type;
        $contragent->name = $this->name;
        $contragent->name_full = $this->name_full;
        $contragent->address_jur = $this->address_jur;
        $contragent->address_post = $this->address_post;
        $contragent->inn = $this->inn;
        $contragent->kpp = $this->kpp;
        $contragent->position = $this->position;
        $contragent->fio = $this->fio;

        if ($contragent->legal_type == "legal")
        {
            if (trim($contragent->name_full) == "")
            {
                $contragent->name_full = $contragent->name;
            }

            if (trim($contragent->address_post) == "")
            {
                $contragent->address_post = $contragent->address_jur;
            }
        }
        if ($contragent->legal_type == "person")
        {
            $contragent->name = $contragent->name_full = $this->first_name." ".$this->last_name.($this->middle_name ? " ".$this->middle_name : "");
        }

        $contragent->save();

        if ($contragent->legal_type == "ip" || $contragent->legal_type == "person")
        {
            $person = $contragent->person;

            if (!$person)
            {
                $person = new ClientContragentPerson();
                $person->contragent_id = $contragent->id;
            }

            $person->first_name = $this->first_name;
            $person->last_name = $this->last_name;
            $person->middle_name = $this->middle_name;

            $person->address = $this->address;

            $person->passport_serial = $this->passport_serial;
            $person->passport_number = $this->passport_number;
            $person->passport_issued = $this->passport_issued;
            $person->passport_date_issued = $this->passport_date_issued;

            $person->save();
        }

        return $contragent->saveToAccount();
    }
}
