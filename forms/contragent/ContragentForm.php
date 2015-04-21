<?php
namespace app\forms\contragent;

use Yii;
use app\classes\Form;
use yii\validators\RangeValidator;

class ContragentForm extends Form
{

    public $legal_type;
    public $name;
    public $name_full;
    public $address_jur;
    public $address_post;
    public $inn;
    public $inn_euro;
    public $kpp;
    public $position;
    public $fio;
    public $tax_regime;
    public $ogrn;
    public $opf;
    public $okpo;
    public $okvd;
    
    public $last_name;
    public $first_name;
    public $middle_name;
    public $passport_serial;
    public $passport_number;
    public $passport_issued;
    public $passport_date_issued;
    public $address;

    public function rules()
    {
        $rules = [];
        $rules[] = [[ 'legal_type', 'name', 'name_full', 'address_jur', 'address_post', 'inn'/*, 'inn_euro'*/,
            'kpp', 'position', 'fio', 'tax_regime', 'opf', 'okpo', 'okvd', 'ogrn'], 'string'];
        $rules[] = [['last_name', 'first_name', 'middle_name', 'passport_serial', 'passport_number', 'passport_issued', 'passport_date_issued'], 'string'];
        $rules[] = [['legal_type'], 'required']; //name? 
        $rules[] = [['legal_type'], 'in', 'range' => ['legal', 'ip', 'person']];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            "name" => "Название контрагента",
            "name_full" => "Полное наименование",
            "address_jur" => "Адрес юридический",
            "address_post" => "Адрес почтовый",
            "legal_type" => "Тип",
            "inn" => "ИНН",
            "inn_euro" => "ЕвроИНН",
            "kpp" => "КПП",
            "position" => "Должность",
            "fio" => "ФИО",
            "tax_regime" => "Налогвый режим",
            "ogrn" => "Код ОГРН",
            "opf" => "Код ОПФ",
            "okpo" => "Код ОКПО",
            "okvd" => "Код ОКВЭД",
        ];
    }
}
