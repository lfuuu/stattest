<?php
namespace app\forms\client;

use app\models\ClientContragent;
use app\models\ClientContragentPerson;
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

    public $ddate = null;

    public $legal_type,
        $name = '',
        $name_full = '',
        $address_jur = '',
        $inn = '',
        $kpp = '',
        $position = '',
        $fio = '',
        $tax_regime,
        $opf = '',
        $okpo = '',
        $okvd = '',
        $ogrn = '',
        $country_id = 643,

        $contragent_id = '',
        $first_name = '',
        $last_name = '',
        $middle_name = '',
        $passport_date_issued = '1970-01-01',
        $passport_serial = '',
        $passport_number = '',
        $passport_issued = '',
        $registration_address = '';

    public function rules()
    {
        $rules = [
            [['legal_type', 'name', 'name_full', 'address_jur', 'inn',
                'kpp', 'position', 'fio', 'tax_regime', 'opf', 'okpo', 'okvd', 'ogrn'], 'string'],
            ['legal_type', 'in', 'range' => ['person', 'ip', 'legal']],
            ['tax_regime', 'in', 'range' => ['simplified', 'full']],
            [['legal_type', 'super_id'], 'required'],
            [['first_name', 'last_name', 'middle_name', 'passport_date_issued', 'passport_serial',
                'passport_number', 'passport_issued', 'registration_address'], 'string'],
            [['super_id', 'country_id'], 'integer'],
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
            $this->contragent = HistoryVersion::getVersionOnDate('ClientContragent', $this->id, $this->ddate);
            if ($this->contragent === null) {
                throw new Exception('Contragent not found');
            }

            $this->person = ClientContragentPerson::find()->where(['contragent_id' => $this->contragent->id])->one();
            if ($this->person === null) {
                $this->person = new ClientContragentPerson();
            }
            $this->setAttributes($this->contragent->getAttributes() + $this->person->getAttributes(), false);
        } elseif ($this->super_id) {
            $this->contragent = new ClientContragent();
            $this->super_id = $this->contragent->super_id = $this->super_id;
            $this->person = new ClientContragentPerson();
        } else
            throw new Exception('You must send id or super_id');
    }

    public function save()
    {
        $contragent = $this->contragent;
        $person = $this->person;

        $contragent->super_id = $this->super_id;
        $contragent->legal_type = $this->legal_type;
        $contragent->name = $this->name;
        $contragent->name_full = $this->name_full;
        $contragent->address_jur = $this->address_jur;
        $contragent->inn = $this->inn;
        $contragent->kpp = $this->kpp;
        $contragent->position = $this->position;
        $contragent->fio = $this->fio;
        $contragent->tax_regime = $this->tax_regime;
        $contragent->opf = $this->opf;
        $contragent->okpo = $this->okpo;
        $contragent->okvd = $this->okvd;
        $contragent->ogrn = $this->ogrn;
        $contragent->country_id = $this->country_id;

        if ($contragent->save()) {
            $this->setAttributes($contragent->getAttributes(), false);
            if ($contragent->legal_type == 'ip' || $contragent->legal_type == 'person') {
                if (!$person->contragent_id)
                    $person->contragent_id = $contragent->id;

                $person->first_name = $this->first_name;
                $person->last_name = $this->last_name;
                $person->middle_name = $this->middle_name;
                $person->passport_date_issued = $this->passport_date_issued;
                $person->passport_serial = $this->passport_serial;
                $person->passport_number = $this->passport_number;
                $person->passport_issued = $this->passport_issued;
                $person->registration_address = $this->registration_address;

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
        $contragent = $this->contragent;
        $person = $this->person;

        $contragent->super_id = $this->super_id;
        $contragent->legal_type = $this->legal_type;
        $contragent->name = $this->name;
        $contragent->name_full = $this->name_full;
        $contragent->address_jur = $this->address_jur;
        $contragent->inn = $this->inn;
        $contragent->kpp = $this->kpp;
        $contragent->position = $this->position;
        $contragent->fio = $this->fio;
        $contragent->tax_regime = $this->tax_regime;
        $contragent->opf = $this->opf;
        $contragent->okpo = $this->okpo;
        $contragent->okvd = $this->okvd;
        $contragent->ogrn = $this->ogrn;
        $contragent->country_id = $this->country_id;
        $contragent->validate() || $this->addErrors($contragent->getErrors());

        if ($contragent->legal_type == 'ip' || $contragent->legal_type == 'person') {
            $person->first_name = $this->first_name;
            $person->last_name = $this->last_name;
            $person->middle_name = $this->middle_name;
            $person->passport_date_issued = $this->passport_date_issued;
            $person->passport_serial = $this->passport_serial;
            $person->passport_number = $this->passport_number;
            $person->passport_issued = $this->passport_issued;
            $person->registration_address = $this->registration_address;

            $person->validate() || $person->getErrors();
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate())
            return false;
        switch ($this->legal_type) {
            case 'legal':
                if (empty($this->name) && !empty($this->name_full))
                    $this->name = $this->name_full;
                elseif (empty($this->name_full) && !empty($this->name))
                    $this->name_full = $this->name;
                break;
            case 'ip':
                $this->name = $this->name_full = $this->last_name . " " . $this->first_name . ($this->middle_name ? " ".$this->middle_name : "");
                break;
            case 'person':
                $this->name = $this->name_full = $this->last_name . " " . $this->first_name . ($this->middle_name ? " ".$this->middle_name : "");
                break;
        }
        return true;
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }
}
