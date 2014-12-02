<?php
namespace app\forms\tariffication;

use app\models\tariffication\Service;
use yii\base\Exception;

class ServiceEditForm extends ServiceForm
{
    public $id;
    public $name;
    public $service_type_id;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id', 'name', 'service_type_id'], 'required'];
        return $rules;
    }

    public function save()
    {
        $item = Service::findOne($this->id);
        if ($item === null) throw new Exception();

        $item->name = $this->name;
        $item->service_type_id = $this->service_type_id;

        return $this->saveModel($item);
    }
}