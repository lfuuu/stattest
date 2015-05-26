<?php
namespace app\forms\billing;

use app\classes\Form;
use app\classes\validators\CurrencyValidator;

class PricelistForm extends Form
{
    public $id;
    public $connection_point_id;
    public $name;
    public $currency_id;
    public $local;
    public $orig;
    public $tariffication_by_minutes;
    public $tariffication_full_first_minute;
    public $initiate_mgmn_cost;
    public $initiate_zona_cost;
    public $local_network_config_id;

    public function rules()
    {
        return [
            [['id','connection_point_id','local_network_config_id'], 'integer'],
            [['name'], 'string'],
            //[['currency_id'], CurrencyValidator::className()],
            //[['currency_id'], 'string', 'length' => 3],
            [['local','orig','tariffication_by_minutes','tariffication_full_first_minute'], 'boolean'],
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
            'local' => 'Местные',
            'orig' => 'оригинация (да), терминация (нет)',
            'tariffication_by_minutes' => 'поминутная (да), посекундная (нет)',
            'tariffication_full_first_minute' => 'Первая минута оплачивается полностью',
            'initiate_mgmn_cost' => 'Инициация МГМН вызова',
            'initiate_zona_cost' => 'Инициация зонового вызова',
        ];
    }
}