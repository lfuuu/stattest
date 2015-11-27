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
    public $disconnecting_date;
    public $tariff_change_date;
    public $no_of_lines;
    public $address;
    public $line7800_id;
    public $address_from_datacenter_id;

    public $mass_change_tariff;
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
            [['mass_change_tariff'], 'boolean'],
            [['tariff_main_id','tariff_local_mob_id','tariff_russia_id','tariff_russia_mob_id','tariff_intern_id'], 'integer'],
            [['tariff_main_status'], 'string'],
            [['tariff_group_local_mob','tariff_group_russia','tariff_group_intern'], 'integer'],
            [['tariff_group_local_mob_price','tariff_group_russia_price','tariff_group_intern_price','tariff_group_price'], 'number'],
            ['status', 'default', 'value' => 'connecting'],
            [['connecting_date'], 'validateDate', 'on' => 'edit']
        ];
    }

    public function attributeLabels()
    {
        return [
            'city_id' => 'Город',
            'connection_point_id' => 'Точка присоединения',
            'type_id' => 'Тип',
            'number_tariff_id' => 'DID группа',
            'connecting_date' => 'Дата подключения',
            'disconnecting_date' => 'Дата отключения',
            'tariff_change_date' => 'Дата изменения тарифа',
            'did' => 'Номер',
            'no_of_lines' => 'Количество линий',
            'address' => 'Адрес',
            'line7800_id' => 'Линия без номера',
            'status' => 'Статус',
            'mass_change_tariff' => 'Массово изменить тариф у всех услуг с этим тарифом',
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

    public function validateDate($attr, $params)
    {
        $expireDt = new \DateTime($this->usage->actual_to.' 23:59:59');
        $nowDt = new \DateTime('now');

        if (!$this->usage->isActive() && $expireDt < $nowDt) {
            $this->addError('disconnecting_date', 'Услуга отключена '.($expireDt->format('d.m.Y')));
        }
    }


}
