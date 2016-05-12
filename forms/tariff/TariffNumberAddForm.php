<?php
namespace app\forms\tariff;

use app\models\TariffNumber;

class TariffNumberAddForm extends TariffNumberForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [
            [
                'country_id',
                'city_id',
                'currency_id',
                'name',
                'status',
                'period',
                'did_group_id',
                'activation_fee',
            ],
            'required',
            'on' => 'save'
        ];
        $rules[] = [['scenario'], 'safe'];
        return $rules;
    }

    public function preProcess()
    {
        !(int) $this->country_id ? $this->city_id = 0 : false;
        !(int) $this->city_id ? $this->did_group_id = 0 : false;
    }

    public function save()
    {
        $item = new TariffNumber();

        $item->country_id = $this->country_id;
        $item->city_id = $this->city_id;
        $item->currency_id = $this->currency_id;
        $item->name = $this->name;
        $item->activation_fee = $this->activation_fee;
        $item->status = $this->status;
        $item->period = $this->period;
        $item->did_group_id = $this->did_group_id;

        return $this->saveModel($item);
    }

}