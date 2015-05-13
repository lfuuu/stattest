<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientContragent extends ActiveRecord
{
    public $cPerson = null;

    public static function tableName()
    {
        return 'client_contragent';
    }
    
    public function rules()
    {
        return [
            [['legal_type', 'name', 'name_full', 'address_jur', 'address_post', 'inn', 'inn_euro',
            'kpp', 'position', 'fio', 'tax_regime', 'opf', 'okpo', 'okvd', 'ogrn'], 'string'],
            ['legal_type', 'in', 'range' => ['person', 'ip', 'legal']],
            ['tax_regime', 'in', 'range' => ['simplified', 'full']],
            ['super_id', 'integer'],
            [['name', 'legal_type', 'super_id'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            "name" => "Краткое наименование",
            "name_full" => "Полное наименование",
            "address_jur" => "Адрес юридический",
            "address_post" => "Адрес почтовый",
            "legal_type" => "Тип",
            "inn" => "ИНН",
            "inn_euro" => "ЕвроИНН",
            "kpp" => "КПП",
            "position" => "Должность Исполнительного органа",
            "fio" => "ФИО Исполнительного органа",
            "tax_regime" => "Налоговый режим",
            "ogrn" => "Код ОГРН",
            "opf" => "Код ОПФ",
            "okpo" => "Код ОКПО",
            "okvd" => "Код ОКВЭД",
        ];
    }
    
    public function getAccounts()
    {
      return $this->hasMany(ClientAccount::className(), ['contragent_id' => 'id']);
    }

    public function getPerson()
    {
      return $this->hasOne(ClientPerson::className(), ['contragent_id' => 'id']);
    }

    function beforeValidate()
    {
        if(!parent::beforeValidate())
            return false;
        switch($this->legal_type){
            case 'legal':
                if(empty($this->name) && !empty($this->name_full))
                    $this->name = $this->name_full;
                break;
            case 'ip':
                $person = $this->cPerson;
                $this->name = $this->name_full = $person->first_name . $person->middle_name + $person->last_name;
                break;
            case 'person':
                $person = $this->cPerson;
                $this->name = $this->name_full = $person->first_name . $person->middle_name + $person->last_name;
                break;
        }
        return true;
    }
}