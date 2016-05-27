<?php

namespace app\forms\lk_wizard;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\models\ClientContragent;
use app\models\ClientAccount;

class WizardContragentEuroForm extends Form
{
    public $is_inn = null;
    public $legal_type;
    public $name;
    public $inn;
    public $address_jur;
    public $address_post;
    public $position;
    public $fio;
    public $last_name;
    public $first_name;
    public $middle_name;
    public $address_birth;
    public $birthday;
    public $address;

    public function rules()
    {
        $rules = [];

        $rules[] = [['legal_type'], 'required'];
        $rules[] = [['middle_name'], 'safe'];
        $rules[] = [['legal_type'], 'in', 'range' => [ClientContragent::LEGAL_TYPE, ClientContragent::PERSON_TYPE]];

        $rules[] = [['address_post'], FormFieldValidator::className()];

        $rules[] = [['is_inn'], 'required', 'when' => function($model){
            return $model->legal_type == ClientContragent::LEGAL_TYPE;
        }];

        $rules[] = [['inn'], 'required', 'when' => function($model){
            return $model->legal_type == ClientContragent::LEGAL_TYPE && $model->is_inn;
        }];

        $rules[] = [['inn'], FormFieldValidator::className(), 'when' => function($model){
            return $model->legal_type == ClientContragent::LEGAL_TYPE && $model->is_inn;
        }];

        foreach (['required', FormFieldValidator::className()] as $validator) {
            $rules[] = [
                ['name', 'address_jur', 'position', 'fio'],
                $validator,
                "when" => function ($model) {
                    return $model->legal_type == ClientContragent::LEGAL_TYPE;
                }
            ];

            $rules[] = [
                ['last_name', 'first_name', 'address_birth', 'birthday', 'address'],
                $validator,
                "when" => function ($model) {
                    return $model->legal_type == ClientContragent::PERSON_TYPE;
                }
            ];
        }

        return $rules;
    }

    public function saveInContragent(ClientAccount $account)
    {
        /*
         *     $legal_type, $name, $inn, $address_jur, $position, $fio - legal fields
         *     $last_name, $first_name, $middle_name, $address_birth, $birthday, $address - person fields
         *
         *     $address_post - legal && person fields
         */

        $contragent = $account->contragent;
        $person = $contragent->person;

        $contragent->legal_type = $this->legal_type;

        if ($contragent->legal_type == ClientContragent::LEGAL_TYPE) {
            if (!$account->address_post) {
                $account->address_post = $contragent->address_jur;
            }

            $contragent->name_full = $contragent->name = $this->name;
            $contragent->address_jur = $this->address_jur;
            $contragent->inn = $this->inn;
            $contragent->position = $this->position;
            $contragent->fio = $this->fio;
        }

        if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $contragent->name = $contragent->name_full = $this->last_name . " " . $this->first_name;

            $person->first_name = $this->first_name;
            $person->last_name = $this->last_name;
            $person->middle_name = $this->middle_name;
            $person->birthplace = $this->address_birth;
            $person->birthday = $this->birthday;
            $person->registration_address = $this->address;
        }

        $account->address_post = $this->address_post;

        $account->save(false);
        $contragent->save(false);

        if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $person->save(false);
            $contragent->refresh();
        }

        return true;
    }
}
