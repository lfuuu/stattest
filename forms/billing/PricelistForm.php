<?php
namespace app\forms\billing;

use app\classes\Form;

class PricelistForm extends Form
{
    public $id;
    public $connection_point_id;
    public $name;
    public $currency_id;
    public $type;
    public $orig;
    public $tariffication_by_minutes;
    public $tariffication_full_first_minute;
    public $initiate_mgmn_cost;
    public $initiate_zona_cost;
    public $local_network_config_id;
    public $price_include_vat;

    public function rules()
    {
        return [
            [['id','connection_point_id','local_network_config_id'], 'integer'],
            [['name'], 'string'],
            [['type'], 'string'],
            [['orig','tariffication_by_minutes','tariffication_full_first_minute','price_include_vat'], 'boolean'],
            [['initiate_mgmn_cost', 'initiate_zona_cost'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'connection_point_id' => 'Точка присоединения',
            'local_network_config_id' => 'Местные префиксы',
            'name' => 'Название',
            'currency_id' => 'Валюта',
            'type' => 'Тип',
            'orig' => 'оригинация (да), терминация (нет)',
            'tariffication_by_minutes' => 'поминутная (да), посекундная (нет)',
            'tariffication_full_first_minute' => 'Первая минута оплачивается полностью',
            'price_include_vat' => 'Цена включает НДС',
            'initiate_mgmn_cost' => 'Инициация МГМН вызова',
            'initiate_zona_cost' => 'Инициация зонового вызова',
        ];
    }
}