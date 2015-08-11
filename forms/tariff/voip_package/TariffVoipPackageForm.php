<?php

namespace app\forms\tariff\voip_package;

use app\classes\Form;
use app\models\TariffVoipPackage;

class TariffVoipPackageForm extends Form
{

    public
        $id,
        $country_id = 0,
        $connection_point_id = 0,
        $currency_id = 0,
        $destination_id = 0,
        $pricelist_id = 0,
        $name = '',
        $price_include_vat = 1,
        $periodical_fee = 0.0000,
        $min_payment = 0,
        $minutes_count = 0;

    public function rules()
    {
        return [
            [['country_id','connection_point_id','currency_id','name',], 'required'],
            [
                [
                    'id','country_id','connection_point_id','destination_id','pricelist_id',
                    'min_payment','minutes_count','price_include_vat'
                ],
                'integer'
            ],
            [['currency_id','name','periodical_fee',], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'country_id' => 'Страна',
            'connection_point_id' => 'Точка подключения',
            'currency_id' => 'Валюта',
            'destination_id' => 'Направление',
            'pricelist_id' => 'Прайс-лист',
            'name' => 'Название',
            'price_include_vat' => 'включить в цену ставку налога',
            'periodical_fee' => 'Абонентская плата',
            'min_payment' => 'Минимальный платеж',
            'minutes_count' => 'Кол-во минут',
        ];
    }

    public function save(TariffVoipPackage $tariff = null)
    {
        if ($tariff === null) {
            $tariff = new TariffVoipPackage;
        }
        $tariff->setAttributes($this->getAttributes(), false);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tariff->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $tariff->id;

        return true;
    }

}