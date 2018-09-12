<?php

namespace app\forms\lk_wizard;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\models\ClientContragent;
use app\models\ClientAccount;
use app\models\ContractType;
use app\models\LkWizardState;

/**
 * Class WizardContragentEuroForm
 */
class WizardContragentEuroForm extends Form
{
    /** Hungary && Slovak wizard */
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

    /** Only Slovak */
    public $ogrn;
    public $passport_number;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];

        $rules[] = [['legal_type'], 'required', 'message' => 'wizard_fill_field'];
        $rules[] = [['middle_name'], 'safe'];
        $rules[] = [['legal_type'], 'in', 'range' => [ClientContragent::LEGAL_TYPE, ClientContragent::PERSON_TYPE, ClientContragent::IP_TYPE], 'message' => 'format_error'];

        $rules[] = [['address_post'], FormFieldValidator::class];

        $rules[] = [
            ['is_inn'],
            'required',
            'when' => function ($model) {
                return $model->legal_type == ClientContragent::LEGAL_TYPE;
            },
            'message' => 'wizard_fill_field'
        ];

        foreach (['required', FormFieldValidator::class] as $validator) {

            $validatorMessageRuleAdd = ($validator == 'required' ? ['message' => 'wizard_fill_field'] : []);

            $rules[] = [
                    ['inn'],
                    $validator,
                    'when' => function ($model) {
                        return $model->legal_type == ClientContragent::LEGAL_TYPE && $model->is_inn;
                    },
                    'message' => 'wizard_fill_field'
                ] + $validatorMessageRuleAdd;

            $rules[] = [
                    ['name', 'address_jur', 'fio'],
                    $validator,
                    'when' => function ($model) {
                        return $model->legal_type == ClientContragent::LEGAL_TYPE;
                    }
                ] + $validatorMessageRuleAdd;

            $rules[] = [
                    ['position'],
                    $validator,
                    'on' => [LkWizardState::TYPE_HUNGARY, LkWizardState::TYPE_AUSTRIA],
                    'when' => function ($model) {
                        return $model->legal_type == ClientContragent::LEGAL_TYPE;
                    }
                ] + $validatorMessageRuleAdd;

            $rules[] = [
                    ['last_name', 'first_name', 'address_birth', 'birthday', 'address'],
                    $validator,
                    'when' => function ($model) {
                        return $model->legal_type == ClientContragent::PERSON_TYPE;
                    }
                ] + $validatorMessageRuleAdd;

            $rules[] = [
                    'ogrn',
                    $validator,
                    'on' => LkWizardState::TYPE_SLOVAK,
                    'when' => function ($model) {
                        return in_array($model->legal_type, [ClientContragent::IP_TYPE, ClientContragent::LEGAL_TYPE]);
                    }
                ] + $validatorMessageRuleAdd;

            $rules[] = [
                    'passport_number',
                    $validator,
                    'on' => [LkWizardState::TYPE_SLOVAK, LkWizardState::TYPE_AUSTRIA],
                    'when' => function ($model) {
                        return $model->legal_type == ClientContragent::PERSON_TYPE;
                    }
                ] + $validatorMessageRuleAdd;

            $rules[] = [
                    ['name', 'last_name', 'first_name', 'inn', 'address_jur', 'fio'],
                    $validator,
                    'on' => LkWizardState::TYPE_SLOVAK,
                    'when' => function ($model) {
                        return $model->legal_type == ClientContragent::IP_TYPE;
                    }
                ] + $validatorMessageRuleAdd;
        }

        return $rules;
    }

    /**
     * @param ClientAccount $account
     * @return bool
     */
    public function saveInContragent(ClientAccount $account)
    {
        /**
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
            if ($this->scenario != LkWizardState::TYPE_AUSTRIA) {
                $person->middle_name = $this->middle_name;
            }
            $person->birthplace = $this->address_birth;
            $person->birthday = $this->birthday;
            $person->registration_address = $this->address;
        }

        $account->address_post = $this->address_post;

        if (in_array($this->scenario, [LkWizardState::TYPE_SLOVAK, LkWizardState::TYPE_AUSTRIA])){
            $contragent->comment = $this->passport_number;
        }

        if ($this->scenario == LkWizardState::TYPE_SLOVAK) {
            $contragent->ogrn = $this->ogrn;
            $person->passport_number = $this->passport_number;

            $contragent->name = $this->name;
            $person->first_name = $this->first_name;
            $person->last_name = $this->last_name;
            $contragent->inn = $this->inn;
            $contragent->fio = $this->fio;
            $contragent->address_jur = $this->address_jur;
        }

        if (!$account->save(false)) {
            throw new ModelValidationException($account);
        }

        if (!$contragent->save(false)) {
            throw new ModelValidationException($contragent);
        }

        if (
            $contragent->legal_type == ClientContragent::PERSON_TYPE
            || ($this->scenario == LkWizardState::TYPE_SLOVAK && ClientContragent::IP_TYPE)
        ) {
            if (!$person->save(false)) {
                throw new ModelValidationException($person);
            }

            $contragent->refresh();
        }

        return true;
    }
}
