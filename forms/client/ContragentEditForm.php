<?php
namespace app\forms\client;

use app\models\ClientContragent;
use app\models\ClientContragentPerson;
use app\models\Country;
use app\models\HistoryVersion;
use Yii;
use app\classes\Form;
use yii\base\Exception;

class ContragentEditForm extends Form
{
    public $id;
    public $super_id;
    protected $person = null,
        $contragent = null;

    public $deferredDate = null;

    public $legal_type,
        $name,
        $name_full,
        $address_jur,
        $inn = "",
        $kpp = "",
        $position = "",
        $fio = "",
        $tax_regime = 1,
        $opf = "",
        $okpo = "",
        $okvd = "",
        $ogrn = "",
        $country_id = Country::RUSSIA,

        $contragent_id,
        $first_name,
        $last_name,
        $middle_name,
        $passport_date_issued,
        $passport_serial,
        $passport_number,
        $passport_issued,
        $registration_address;

    public function rules()
    {
        $rules = [
            [['legal_type', 'super_id'], 'required'],
            [['name', 'name_full', 'address_jur', 'inn',
                'kpp', 'position', 'fio', 'opf', 'okpo', 'okvd', 'ogrn'], 'string'],
            [['name', 'name_full', 'address_jur', 'inn',
                'kpp', 'position', 'fio', 'opf', 'okpo', 'okvd', 'ogrn'], 'default', 'value' => ''],

            [['first_name', 'last_name', 'middle_name', 'passport_date_issued', 'passport_serial',
                'passport_number', 'passport_issued', 'registration_address'], 'string'],
            [['first_name', 'last_name', 'middle_name', 'passport_serial',
                'passport_number', 'passport_issued', 'registration_address'], 'default', 'value' => ''],
            ['passport_date_issued', 'default', 'value' => '1970-01-01'],
            ['tax_regime', 'default', 'value' => '0'],

            ['legal_type', 'in', 'range' => array_keys(ClientContragent::$legalTypes)],
            [['super_id', 'country_id', 'tax_regime'], 'integer'],

        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientContragent())->attributeLabels() + (new ClientContragentPerson())->attributeLabels();
    }

    public function init()
    {
        if ($this->id) {
            $this->contragent = ClientContragent::findOne($this->id)->loadVersionOnDate($this->deferredDate);
            if ($this->contragent === null) {
                throw new Exception('Contragent not found');
            }

            $person = ClientContragentPerson::findOne(['contragent_id' => $this->contragent->id]);
            if($person)
                $this->person = $person->loadVersionOnDate($this->deferredDate);
            else
                $this->person = new ClientContragentPerson();

            $this->setAttributes($this->contragent->getAttributes() + $this->person->getAttributes(), false);
        } else {
            $this->contragent = new ClientContragent();
            $this->person = new ClientContragentPerson();
        }
    }

    public function save()
    {
        $this->fillContragentNameByLegalType();
        $this->fillContragent();
        $contragent = $this->contragent;
        if ($contragent->save()) {
            $this->setAttributes($contragent->getAttributes(), false);
            if ($contragent->legal_type == 'ip' || $contragent->legal_type == 'person') {
                $this->fillPerson();
                $person = $this->person;
                if (!$person->contragent_id)
                    $person->contragent_id = $contragent->id;

                if ($person->save()) {
                    $contragent->refresh();
                    return true;
                } else {
                    $this->addErrors($person->getErrors());
                    $contragent->delete();
                }
            }
            return true;
        } else
            $this->addErrors($contragent->getErrors());

        return false;
    }

    public function validate($attributeNames = null, $clearErrors = false)
    {
        $this->fillContragent();
        $contragent = $this->contragent;
        $contragent->validate() || $this->addErrors($contragent->getErrors());

        if ($contragent->legal_type == ClientContragent::IP_TYPE || $contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $this->fillPerson();
            $person = $this->person;
            $person->validate() || $person->getErrors();
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }

    private function fillContragentNameByLegalType()
    {
        switch ($this->legal_type) {
            case ClientContragent::LEGAL_TYPE:
                if (empty($this->name) && !empty($this->name_full))
                    $this->name = $this->name_full;
                elseif (empty($this->name_full) && !empty($this->name))
                    $this->name_full = $this->name;
                break;
            case ClientContragent::IP_TYPE:
                $this->name = $this->name_full = $this->last_name . " " . $this->first_name . ($this->middle_name ? " " . $this->middle_name : "");
                break;
            case ClientContragent::PERSON_TYPE:
                $this->name = $this->name_full = $this->last_name . " " . $this->first_name . ($this->middle_name ? " " . $this->middle_name : "");
                break;
        }
    }

    private function fillContragent()
    {
        $contragent = &$this->contragent;
        $contragent->super_id = $this->super_id;
        $contragent->legal_type = $this->legal_type;
        $contragent->name = $this->name;
        $contragent->name_full = $this->name_full;
        $contragent->address_jur = $this->address_jur;
        $contragent->inn = $this->inn;
        $contragent->kpp = $this->kpp;
        $contragent->position = $this->position;
        $contragent->fio = $this->fio;

        if ($contragent->legal_type == 'person')
            $contragent->tax_regime = 'simplified';
        else
            $contragent->tax_regime = $this->tax_regime;

        $contragent->opf = $this->opf;
        $contragent->okpo = $this->okpo;
        $contragent->okvd = $this->okvd;
        $contragent->ogrn = $this->ogrn;
        $contragent->country_id = $this->country_id;
    }

    private function fillPerson()
    {
        $person = &$this->person;
        $person->first_name = $this->first_name;
        $person->last_name = $this->last_name;
        $person->middle_name = $this->middle_name;
        $person->passport_date_issued = $this->passport_date_issued;
        $person->passport_serial = $this->passport_serial;
        $person->passport_number = $this->passport_number;
        $person->passport_issued = $this->passport_issued;
        $person->registration_address = $this->registration_address;
    }
}
