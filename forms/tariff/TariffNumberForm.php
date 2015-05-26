<?php
namespace app\forms\tariff;

use app\classes\Form;

class TariffNumberForm extends Form
{
    public $id;
    public $country_id;
    public $currency_id;
    public $city_id;
    public $connection_point_id;
    public $name;
    public $status;
    public $activation_fee;
    public $periodical_fee;
    public $period;
    public $did_group_id;

    public function rules()
    {
        return [
            [['id','country_id','city_id','connection_point_id'], 'integer'],
            [['currency_id','name','status','period'], 'string'],
            [['activation_fee','periodical_fee'], 'number'],
            [['did_group_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'country_id' => 'Страна',
            'currency_id' => 'Валюта',
            'city_id' => 'Город',
            'connection_point_id' => 'Точка подключения',
            'name' => 'Название',
            'status' => 'Статус',
            'activation_fee' => 'Плата за подключение',
            'periodical_fee' => 'Абонентская плата',
            'period' => 'Период',
            'did_group_id' => 'DID группа',
        ];
    }
}