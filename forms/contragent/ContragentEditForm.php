<?php
namespace app\forms\contragent;

use app\classes\Connection;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\ClientContragentPerson;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use yii\db\Command;
use yii\db\Query;

class ContragentEditForm extends Form
{
    public $id;
    protected $person = null;
    protected $contragent = null;

    public $legal_type,
        $name,
        $name_full,
        $address_jur,
        $inn,
        $kpp,
        $position,
        $fio,
        $tax_regime,
        $opf,
        $okpo,
        $okvd,
        $ogrn,

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
            [['legal_type', 'name', 'name_full', 'address_jur', 'inn', 'inn_euro',
                'kpp', 'position', 'fio', 'tax_regime', 'opf', 'okpo', 'okvd', 'ogrn'], 'string'],
            ['legal_type', 'in', 'range' => ['person', 'ip', 'legal']],
            ['tax_regime', 'in', 'range' => ['simplified', 'full']],
            ['super_id', 'integer'],
            [['name', 'legal_type', 'super_id'], 'required'],
            [['first_name', 'last_name', 'middle_name', 'passport_date_issued', 'passport_serial',
                'passport_number', 'passport_issued', 'registration_address'], 'string'],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientContragent())->attributeLabels() + (new ClientContragentPerson())->attributeLabels();
    }

    public function init()
    {

        $c = ClientAccount::find()->one();
        $fds = array_keys($c->getAttributes());
        foreach($fds as $f)
            echo "UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/$f-]','\"'),'[-$f-]','\"') WHERE `model` = 'Client';"."<br/>";

        die();
        if ($this->id) {
            $this->contragent = ClientContragent::findOne($this->id);
            if ($this->contragent === null) {
                throw new Exception('Contragent not found');
            }

            $this->person = ClientContragentPerson::findOne($this->contragent->id);
            if ($this->person === null) {
                $this->person = new ClientContragentPerson();
            }
            $this->setAttributes($this->contragent->getAttributes() + $this->person->getAttributes(), false);
        } else {
            $this->contragent = new ClientContragent();
            $this->person = new ClientContragentPerson();
        }
    }

    public function save()
    {
        $contragent = $this->contragent;
        $person = $this->person;

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

        if ($contragent->save()) {
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
                return true;
            }
        }
        return false;
    }

    function beforeValidate()
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
                $this->name = $this->name_full = $this->first_name . $this->middle_name + $this->last_name;
                break;
            case 'person':
                $this->name = $this->name_full = $this->first_name . $this->middle_name + $this->last_name;
                break;
        }
        return true;
    }
}
