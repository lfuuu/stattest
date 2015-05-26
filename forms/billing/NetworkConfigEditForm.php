<?php
namespace app\forms\billing;

use app\classes\Assert;
use app\classes\Form;
use app\models\billing\NetworkConfig;

class NetworkConfigEditForm extends NetworkConfigForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id','name'], 'required'];
        return $rules;
    }

    public function save()
    {
        $networkConfig = NetworkConfig::findOne($this->id);
        Assert::isObject($networkConfig);

        $networkConfig->name = $this->name;

        $networkConfig->save();

        return true;
    }
}