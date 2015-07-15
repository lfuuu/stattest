<?php
namespace app\forms\voip\prefixlist;

use app\classes\Form;

class PrefixlistForm extends Form
{

    public $id;
    public $name;
    public $type_id;
    public $prefixes;
    public $country_id;
    public $region_id;
    public $city_id;
    public $exclude_operators;
    public $operators;

    public function rules()
    {
        return [
            [['type_id','country_id','region_id','city_id','exclude_operators',], 'integer'],
            [['name',], 'string'],
            ['operators', 'each', 'rule' => ['integer']],
            ['prefixes', 'match', 'pattern' => '/[\d\[\],]+/'],
            ['country_id', 'required', 'when' => function($model) { return $model->type_id == 3; }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'type_id' => 'Тип',
            'country_id' => 'Страна',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'prefixes' => 'Префиксы',
            'operators' => 'Операторы',
            'exclude_operators' => 'Выбор операторов',
        ];
    }



}