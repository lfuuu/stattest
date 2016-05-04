<?php
namespace app\forms\lk_wizard;

use app\classes\Form;
use app\models\ClientContragent;
use app\models\LkWizardState;

/**
 * Класс-форма сохранения "галочек" европейского визарда.
 *
 * Class AcceptsForm
 * @package app\forms\lk_wizard
 */
class AcceptsForm extends Form
{
    public $legal_type = '';
    public $step = 0;

    public $is_rules_accept_legal;
    public $is_rules_accept_person;
    public $is_contract_accept;

    public function rules()
    {
        $rules = [];
        $rules[] = [['legal_type', 'step'], 'safe'];

        foreach (['required', 'boolean'] as $validator) {
            $rules[] = [
                "is_rules_accept_legal",
                $validator,
                "when" => function () {
                    return $this->isNeedCheck('is_rules_accept_legal');
                }
            ];
            $rules[] = [
                "is_rules_accept_person",
                $validator,
                "when" => function () {
                    return $this->isNeedCheck('is_rules_accept_person');
                }
            ];
            $rules[] = [
                "is_contract_accept",
                $validator,
                "when" => function () {
                    return $this->isNeedCheck('is_contract_accept');
                }
            ];
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            "is_rules_accept_legal" => "Согласие с правилами (организация)",
            "is_rules_accept_person" => "Согласие с правилами (частное лицо)",
            "is_contract_accept" => "Принятие условий договора"
        ];
    }

    /**
     * Проверяет необходимость проверки и созранения той или иной "галочки"
     *
     * @param $field
     * @return bool
     */
    public function isNeedCheck($field)
    {
        switch ($field) {
            case 'is_rules_accept_legal': {
                return $this->step == 1 && $this->legal_type == ClientContragent::LEGAL_TYPE;
            }
            case 'is_rules_accept_person': {
                return $this->step == 1 && $this->legal_type == ClientContragent::PERSON_TYPE;
            }
            case 'is_contract_accept': {
                return $this->step == 2;
            }
            default: {
                return false;
            }
        }
    }

    public function save(LkWizardState $wizard)
    {
        if ($this->isNeedCheck('is_rules_accept_legal')) {
            $wizard->is_rules_accept_legal = $this->is_rules_accept_legal;
        }

        if ($this->isNeedCheck('is_rules_accept_person')) {
            $wizard->is_rules_accept_person = $this->is_rules_accept_person;
        }

        if ($this->isNeedCheck('is_contract_accept')) {
            $wizard->is_contract_accept = $this->is_contract_accept;
        }

        return $wizard->save();
    }
}