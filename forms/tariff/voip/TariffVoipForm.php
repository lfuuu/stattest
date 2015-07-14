<?php

namespace app\forms\tariff\voip;

use app\classes\Form;

class TariffVoipForm extends Form
{

    public $id;
    public $country_id;
    public $connection_point_id;
    public $currency_id;
    public $pricelist_id;
    public $name;
    public $name_short;
    public $status;
    public $month_line;
    public $month_number;
    public $once_line;
    public $once_number;
    public $free_local_min;
    public $freemin_for_number;
    public $month_min_payment;
    public $dest;
    public $paid_redirect;
    public $tariffication_by_minutes;
    public $tariffication_full_first_minute;
    public $tariffication_free_first_seconds;
    public $is_virtual;
    public $is_testing;
    public $price_include_vat;

    public function rules()
    {
        return [
            [['country_id','connection_point_id','dest','currency_id','name',], 'required'],
            [
                [
                    'id','connection_point_id','pricelist_id', 'is_virtual', 'is_testing', 'price_include_vat',
                    'month_line','month_number','once_line','once_number','free_local_min','freemin_for_number','month_min_payment',
                    'dest', 'paid_redirect', 'tariffication_by_minutes', 'tariffication_full_first_minute', 'tariffication_free_first_seconds',
                ],
                'integer'
            ],
            [['currency_id','name','name_short','status',], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'country_id' => 'Страна',
            'connection_point_id' => 'Точка подключения',
            'currency_id' => 'Валюта',
            'dest' => 'Направление',
            'status' => 'Состояние',
            'pricelist_id' => 'Прайс-лист',
            'name' => 'Название',
            'name_short' => 'Краткое название',
            'month_line' => 'ежемесячная плата за линию',
            'month_number' => 'ежемесячная плата за номер',
            'month_min_payment' => 'минимальный платеж',
            'once_line' => 'плата за подключение линии',
            'once_number' => 'плата за подключение номера',
            'free_local_min' => 'бесплатных местных минут',
            'freemin_for_number' => 'бесплатные минуты для номера (да) или для линии (нет)',
            'paid_redirect' => 'платные переадресации',
            'tariffication_by_minutes' => 'тарификация: поминутная (да), посекундная (нет)',
            'tariffication_full_first_minute' => 'тарификация: первая минута оплачивается полностью',
            'tariffication_free_first_seconds' => 'тарификация: первые 5 секунд бесплатно',
            'is_virtual' => 'тариф для виртуальных номеров',
            'is_testing' => 'тариф по-умолчанию',
            'price_include_vat' => 'включить в цену ставку налога',
        ];
    }

}