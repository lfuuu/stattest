<?php
namespace app\forms\tariffication;

use app\classes\Form;

class ServiceForm extends Form
{
    public $id;
    public $name;
    public $service_type_id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string', 'min' => 5, 'max' => 100],
            [['service_type_id'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'service_type_id' => 'Тип услуги',
        ];
    }

}