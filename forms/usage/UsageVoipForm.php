<?php
namespace app\forms\usage;

use app\classes\Form;

class UsageVoipForm extends Form
{
    public $id;
    public $client_account_id;
    public $city_id;
    public $connection_point_id;
    public $type_id;
    public $status;
    public $did;
    public $number_tariff_id;
    public $connecting_date;
    public $tariff_change_date;
    public $no_of_lines;
    public $address;
    public $line7800_id;

    public $tariff_main_status;
    public $tariff_main_id;
    public $tariff_local_mob_id;
    public $tariff_russia_id;
    public $tariff_russia_mob_id;
    public $tariff_intern_id;
    public $tariff_group_local_mob;
    public $tariff_group_russia;
    public $tariff_group_intern;
    public $tariff_group_local_mob_price;
    public $tariff_group_russia_price;
    public $tariff_group_intern_price;
    public $tariff_group_price;

    public function rules()
    {
        return [
            [['id','client_account_id','city_id','connection_point_id','number_tariff_id','line7800_id','no_of_lines'], 'integer'],
            [['type_id','did','connecting_date','tariff_change_date','address', 'status'], 'string'],
            [['tariff_main_id','tariff_local_mob_id','tariff_russia_id','tariff_russia_mob_id','tariff_intern_id'], 'integer'],
            [['tariff_main_status'], 'string'],
            [['tariff_group_local_mob','tariff_group_russia','tariff_group_intern'], 'integer'],
            [['tariff_group_local_mob_price','tariff_group_russia_price','tariff_group_intern_price','tariff_group_price'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'city_id' => 'Город',
            'connection_point_id' => 'Точка присоединения',
            'type_id' => 'Тип',
            'number_tariff_id' => 'Тип номера',
            'connecting_date' => 'Дата подключения',
            'tariff_change_date' => 'Дата изменения тарифа',
            'did' => 'Номер',
            'no_of_lines' => 'Количество линий',
            'address' => 'Адрес',
            'line7800_id' => 'Линия без номера',

            'tariff_main_status' => 'Тип тарифа',
            'tariff_main_id' => 'Тариф Основной',
            'tariff_local_mob_id' => 'Тариф Местные мобильные',
            'tariff_russia_id' => 'Тариф Россия стационарные',
            'tariff_russia_mob_id' => 'Тариф Россия мобильные',
            'tariff_intern_id' => 'Тариф Международка',
            'tariff_group_local_mob' => 'Набор',
            'tariff_group_russia' => 'Набор',
            'tariff_group_intern' => 'Набор',
            'tariff_group_local_mob_price' => 'Гарантированный платеж',
            'tariff_group_russia_price' => 'Гарантированный платеж',
            'tariff_group_intern_price' => 'Гарантированный платеж',
            'tariff_group_price' => 'Гарантированный платеж (Набор)',
        ];
    }

}