<?php
namespace app\forms\tariff;

use app\models\TariffNumber;

class TariffNumberAddForm extends TariffNumberForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['country_id', 'city_id', 'connection_point_id', 'currency_id','name','status','period','did_group_id','activation_fee','periodical_fee'], 'required', 'on' => 'save'];
        $rules[] = [['scenario'], 'safe'];
        return $rules;
    }

    public function save()
    {
        $item = new TariffNumber();

        $item->country_id = $this->country_id;
        $item->city_id = $this->city_id;
        $item->connection_point_id = $this->connection_point_id;
        $item->currency_id = $this->currency_id;
        $item->name = $this->name;
        $item->activation_fee = $this->activation_fee;
        $item->periodical_fee = $this->periodical_fee;
        $item->status = $this->status;
        $item->period = $this->period;
        $item->did_group_id = $this->did_group_id;

        return $this->saveModel($item);
    }

}