<?php
namespace app\forms\tariff;

use app\classes\Assert;
use app\models\TariffNumber;

class TariffNumberEditForm extends TariffNumberForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name','status','period','did_group_id','activation_fee','periodical_fee'], 'required', 'on' => 'save'];
        $rules[] = [['scenario'], 'safe'];
        return $rules;
    }

    public function save()
    {
        $item = TariffNumber::findOne($this->id);
        Assert::isObject($item);

        $item->name = $this->name;
        $item->activation_fee = $this->activation_fee;
        $item->periodical_fee = $this->periodical_fee;
        $item->status = $this->status;
        $item->period = $this->period;
        $item->did_group_id = $this->did_group_id;

        return $this->saveModel($item);
    }

}