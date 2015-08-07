<?php
namespace app\forms\tariff;

use app\classes\Form;

class DidGroupForm extends Form
{
    public $id;
    public $name;
    public $city_id;
    public $beauty_level;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['city_id'], 'integer'],
            [['beauty_level'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'city_id' => 'Город',
            'name' => 'Название',
            'beauty_level' => 'Красивость',
        ];
    }
}