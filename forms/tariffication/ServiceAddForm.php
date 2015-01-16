<?php
namespace app\forms\tariffication;

use app\models\tariffication\Service;

class ServiceAddForm extends ServiceForm
{
    public $id;
    public $name;
    public $service_type_id;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name', 'service_type_id'], 'required'];
        return $rules;
    }

    public function save()
    {
        $item = new Service();
        $item->name = $this->name;
        $item->service_type_id = $this->service_type_id;

        return $this->saveModel($item);
    }
}