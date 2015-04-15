<?php
namespace app\forms\contragent;

use Yii;
use app\classes\Form;

class ContragentEditForm extends Form
{

    public $legal_type;
    public $name;
    public $name_full;
    public $address;
    public $inn;
    public $inn_eu;
    public $kpp;
    public $position;
    public $fio;
    public $tax_regime;
    public $opf;
    public $okpo;
    public $okvd;


    public function rules()
    {
        $rules = [];
        $rules[] = [[ 'legal_type', 'name', 'name_full', 'address', 'inn', 'inn_eu', 
            'kpp', 'position', 'fio', 'tax_regime', 'opf', 'okpo', 'okvd'], 'string'];
        $rules[] = [['name', 'legal_type'], 'required'];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            "name" => "Название контрагента",
            "name_full" => "Полное наименование",
            "address" => "Адрес",
            "legal_type" => "Тип",
            "inn" => "ИНН",
            "inn_eu" => "Общеевропейский ИНН",
            "kpp" => "КПП",
            "position" => "Должность",
            "fio" => "ФИО",
            "tax_regime" => "Налогвый режим",
            "opf" => "Код ОПФ",
            "okpo" => "Код ОКПО",
            "okvd" => "Код ОКВЭД"
        ];
    }

    public function save()
    {
        /*
        $client = ClientAccount::findOne($this->client_id);
        Assert::isObject($client);

        $item = new Payment();
        $item->client_id = $this->client_id;
        $item->payment_date = $this->payment_date;
        $item->payment_no = $this->payment_no ?: 0;
        $item->oper_date = $this->oper_date;
        $item->bill_no = $this->bill_no;
        $item->bill_vis_no = $this->bill_no;
        $item->original_currency = $this->original_currency;
        $item->currency = $client->currency;
        $item->original_sum = round($this->original_sum, 2);
        $item->sum = round($this->sum, 2);
        $item->payment_rate = round($item->original_sum / $item->sum, 8);
        $item->type = $this->type;
        $item->bank = $item->type == 'bank' ? $this->bank : 'mos';
        $item->ecash_operator = $item->type == 'ecash' ? $this->ecash_operator : null;
        $item->comment = $this->comment;
        $item->add_date = (new \DateTime())->format(\DateTime::ATOM);
        $item->add_user = \Yii::$app->user->getId();

        $result = $this->saveModel($item);
        if ($result) {
            ClientAccount::dao()->updateBalance($client->id);
        }
        return $result;
         */
    }
}
