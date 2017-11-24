<?php
namespace app\forms\lk_wizard;

use app\classes\Form;
use app\models\ClientContragent;
use app\models\LkWizardState;

/**
 * Класс-форма сохранения "галочек" европейского визарда.
 *
 * Class AcceptsForm
 */
class AcceptsForm extends Form
{
    public $legal_type = '';
    public $step = 0;

    public $is_rules_accept_legal;
    public $is_rules_accept_person;
    public $is_rules_accept_ip;
    public $is_contract_accept;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules[] = [['legal_type', 'step'], 'safe'];

        $rules[] = [
            ['is_rules_accept_legal', 'is_rules_accept_person', 'is_contract_accept',],
            'required',
            'message' => 'wizard_fill_field',
            'when' => function ($model, $attribute) {
                return $this->isNeedCheck($attribute);
            }
        ];

        $rules[] = [
            ['is_rules_accept_legal', 'is_rules_accept_person', 'is_contract_accept',],
            'boolean',
            'message' => 'format_error',
            'when' => function ($model, $attribute) {
                return $this->isNeedCheck($attribute);
            }
        ];

        $rules[] = [
            'is_rules_accept_ip',
            'required',
            'message' => 'wizard_fill_field',
            'on' => 'slovak',
            'when' => function ($model, $attribute) {
                return $this->isNeedCheck($attribute);
            }
        ];

        $rules[] = [
            'is_rules_accept_ip',
            'boolean',
            'message' => 'format_error',
            'on' => 'slovak',
            'when' => function ($model, $attribute) {
                return $this->isNeedCheck($attribute);
            }
        ];


        return $rules;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            "is_rules_accept_legal" => "Согласие с правилами (организация)",
            "is_rules_accept_person" => "Согласие с правилами (частное лицо)",
            "is_rules_accept_ip" => "Согласие с правилами (ИП)",
            "is_contract_accept" => "Принятие условий договора"
        ];
    }

    /**
     * Проверяет необходимость проверки и созранения той или иной "галочки"
     *
     * @param string $field
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
            case 'is_rules_accept_ip': {
                return $this->step == 1 && $this->legal_type == ClientContragent::IP_TYPE;
            }
            case 'is_contract_accept': {
                return $this->step == 2;
            }
            default: {
                return false;
            }
        }
    }

    /**
     * @param LkWizardState $wizard
     * @return bool
     */
    public function save(LkWizardState $wizard)
    {
        if ($this->isNeedCheck('is_rules_accept_legal')) {
            $wizard->is_rules_accept_legal = $this->is_rules_accept_legal;
        }

        if ($this->isNeedCheck('is_rules_accept_person')) {
            $wizard->is_rules_accept_person = $this->is_rules_accept_person;
        }

        if ($this->isNeedCheck('is_rules_accept_ip')) {
            $wizard->is_rules_accept_ip = $this->is_rules_accept_ip;
        }

        if ($this->isNeedCheck('is_contract_accept')) {
            $wizard->is_contract_accept = $this->is_contract_accept;
        }

        return $wizard->save();
    }
}