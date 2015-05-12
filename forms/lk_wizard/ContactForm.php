<?php
namespace app\forms\lk_wizard;

use app\classes\Form;
use app\models\ClientContact;
use app\models\User;

class ContactForm extends Form
{
    public $contact_phone;
    public $contact_fio;

    public function rules()
    {
        $rules = [];

        $rules[] = [["contact_phone"], "string"];
        $rules[] = ["contact_fio", "string"];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            "contact_phone" => "Контактный номер",
            "contact_fio" => "Контактное ФИО"
        ];
    }

    public function save($account)
    {
        $contact = ClientContact::findOne([
            "client_id" => $account->id, 
            "user_id"   => User::CLIENT_USER_ID, 
            "type"      => "phone"
        ]);

        if (!$contact)
        {
            $contact = new ClientContact;
            $contact->client_id = $account->id;
            $contact->user_id = User::CLIENT_USER_ID;
            $contact->type = "phone";
        }
        $contact->data = $this->contact_phone;
        $contact->comment = $this->contact_fio;
        return $contact->save();
    }
}
