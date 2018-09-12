<?php
namespace app\forms\lk_wizard;

use app\classes\Form;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\LkWizardState;
use app\models\User;

/**
 * Class ContactForm
 */
class ContactForm extends Form
{
    public $contact_phone;
    public $contact_fio;
    public $fio;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];

        $rules[] = [['contact_phone'], 'required', 'message' => 'wizard_fill_field'];
        $rules[] = ['contact_fio', 'required', 'on' => 'mcn', 'message' => 'wizard_fill_field'];
        $rules[] = ['fio', 'required', 'on' => [LkWizardState::TYPE_HUNGARY, LkWizardState::TYPE_SLOVAK, LkWizardState::TYPE_AUSTRIA], 'message' => 'wizard_fill_field'];

        return $rules;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'contact_phone' => 'Контактный номер',
            'contact_fio' => 'Контактное ФИО',
            'fio' => 'Контактное ФИО'
        ];
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $result = parent::validate($attributeNames, $clearErrors);

        if (!$result) {
            return $result;
        }

        $contact = new ClientContact;
        $contact->client_id = 1;
        $contact->user_id = User::CLIENT_USER_ID;
        $contact->type = ClientContact::TYPE_PHONE;
        $contact->data = $this->contact_phone;

        if (!$contact->validate()) {
            $this->addError('contact_phone', 'format_error');

            return false;
        }

        return true;
    }

    /**
     * @param ClientAccount $account
     * @return bool
     */
    public function save(ClientAccount $account)
    {
        $contact = ClientContact::findOne([
            'client_id' => $account->id,
            'user_id' => User::CLIENT_USER_ID,
            'type' => ClientContact::TYPE_PHONE,
        ]);

        if (!$contact) {
            $contact = new ClientContact;
            $contact->client_id = $account->id;
            $contact->user_id = User::CLIENT_USER_ID;
            $contact->type = ClientContact::TYPE_PHONE;
        }

        $contact->data = $this->contact_phone;
        $contact->comment = ($this->getScenario() == 'mcn' ? $this->contact_fio : $this->fio);

        return $contact->save();
    }
}
