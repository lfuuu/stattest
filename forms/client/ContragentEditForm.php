<?php
namespace app\forms\client;

use app\classes\DoubleAttributeLabelTrait;
use app\models\ClientContragent;
use app\models\ClientContragentPerson;
use app\models\Country;
use Yii;
use app\classes\Form;
use yii\base\Exception;

class ContragentEditForm extends Form
{
    use DoubleAttributeLabelTrait;

    protected function getLangCategory()
    {
        return 'contragent';
    }

    public $id;
    public $super_id;
    protected $person = null,
        $contragent = null;

    public $historyVersionRequestedDate = null;
    public $historyVersionStoredDate = null;

    public $legal_type,
        $name,
        $name_full,
        $address_jur,
        $inn,
        $kpp,
        $position,
        $fio,
        $tax_regime,
        $opf_id,
        $okpo,
        $okvd,
        $ogrn,
        $country_id,
        $signer_passport,
        $comment,
        $partner_contract_id,
        $sale_channel_id,

        $contragent_id,
        $first_name,
        $last_name,
        $middle_name,
        $passport_date_issued,
        $passport_serial,
        $passport_number,
        $passport_issued,
        $registration_address,
        $mother_maiden_name,
        $birthplace,
        $birthday,
        $other_document;

    public function rules()
    {
        $rules = [
            [['legal_type', 'super_id'], 'required'],
            [['name', 'name_full', 'address_jur', 'inn',
                'kpp', 'position', 'fio', 'okpo', 'okvd', 'ogrn', 'signer_passport', 'comment', 'tax_regime'], 'string'],
            [['name', 'name_full', 'address_jur', 'inn',
                'kpp', 'position', 'fio', 'okpo', 'okvd', 'ogrn', 'signer_passport', 'comment'], 'default', 'value' => ''],

            [['first_name', 'last_name', 'middle_name', 'passport_date_issued', 'passport_serial',
                'passport_number', 'passport_issued', 'registration_address', 'historyVersionStoredDate',
                'mother_maiden_name', 'birthplace', 'birthday', 'other_document',], 'string'],
            [['first_name', 'last_name', 'middle_name', 'passport_serial',
                'passport_number', 'passport_issued', 'registration_address',
                'mother_maiden_name', 'birthplace', 'birthday', 'other_document',], 'default', 'value' => ''],
            ['passport_date_issued', 'default', 'value' => '1970-01-01'],
            [['opf_id', 'sale_channel_id'], 'default', 'value' => 0],
            [['tax_regime'], 'default', 'value' => ClientContragent::TAX_REGTIME_UNDEFINED],
            ['country_id', 'default', 'value' => Country::RUSSIA],

            ['legal_type', 'in', 'range' => [ClientContragent::IP_TYPE, ClientContragent::PERSON_TYPE, ClientContragent::LEGAL_TYPE]],
            [['super_id', 'country_id', 'opf_id', 'partner_contract_id', 'sale_channel_id'], 'integer'],

        ];
        return $rules;
    }

    public function init()
    {
        if ($this->id) {
            $this->contragent = ClientContragent::findOne($this->id);
            if ($this->contragent && $this->historyVersionRequestedDate) {
                $this->contragent->loadVersionOnDate($this->historyVersionRequestedDate);
            }
            if ($this->contragent === null) {
                throw new Exception('Contragent not found');
            }

            $this->person = ClientContragentPerson::findOne(['contragent_id' => $this->contragent->id]);
            if ($this->person) {
                if ($this->historyVersionRequestedDate) {
                    $this->person->loadVersionOnDate($this->historyVersionRequestedDate);
                }
            } else
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
        if ($this->contragent && $this->historyVersionStoredDate) {
            $this->contragent->setHistoryVersionStoredDate($this->historyVersionStoredDate);
        }
        $contragent = $this->contragent;
        if ($contragent->save()) {
            $this->setAttributes($contragent->getAttributes(), false);
            if ($contragent->legal_type == 'ip' || $contragent->legal_type == 'person') {
                $this->fillPerson();
                if ($this->historyVersionStoredDate) {
                    $this->person->setHistoryVersionStoredDate($this->historyVersionStoredDate);
                }
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

    public function getPersonId()
    {
        if ($this->person) {
            return $this->person->id;
        }
        return false;
    }

    /**
     * @return ClientContragent
     */
    public function getContragentModel()
    {
        return $this->contragent;
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
                $name = $this->last_name . " " . $this->first_name . ($this->middle_name ? " " . $this->middle_name : "");
                $this->name = 'ИП ' . $name;
                $this->name_full = 'ИП ' . $name;
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
        $contragent->comment = $this->comment;

        if ($contragent->legal_type == 'person')
            $contragent->tax_regime = ClientContragent::TAX_REGTIME_UNDEFINED;
        else
            $contragent->tax_regime = $this->tax_regime;

        $contragent->opf_id = $this->opf_id;
        $contragent->okpo = $this->okpo;
        $contragent->okvd = $this->okvd;
        $contragent->ogrn = $this->ogrn;
        $contragent->country_id = $this->country_id;
        $contragent->sale_channel_id = $this->sale_channel_id;
        $contragent->partner_contract_id = $this->partner_contract_id;
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
        $person->birthplace = $this->birthplace;
        $person->birthday = $this->birthday;
        $person->mother_maiden_name = $this->mother_maiden_name;
    }
}
