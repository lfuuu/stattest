<?php

namespace app\forms\tariff\voip;

use app\classes\Form;
use app\models\TariffVoip;

class TariffVoipForm extends Form
{

    public
        $id,
        $country_id = 0,
        $connection_point_id = 0,
        $currency_id = 0,
        $pricelist_id = 0,
        $name = '',
        $name_short = '',
        $status = 'public',
        $month_line = 0,
        $month_number = 0,
        $once_line = 0,
        $once_number = 0,
        $free_local_min = 0,
        $freemin_for_number = 1,
        $month_min_payment = 0,
        $dest = 0,
        $paid_redirect = 1,
        $tariffication_by_minutes = 0,
        $tariffication_full_first_minute = 0,
        $tariffication_free_first_seconds = 0,
        $is_virtual = 0,
        $is_testing = 0,
        $price_include_vat = 1,
        $edit_user = 0,
        $edit_time = '';

    public function rules()
    {
        return [
            [['country_id','connection_point_id','dest','currency_id','name',], 'required'],
            [
                [
                    'id','connection_point_id','pricelist_id', 'is_virtual', 'is_testing', 'price_include_vat',
                    'free_local_min','freemin_for_number',
                    'dest', 'paid_redirect', 'tariffication_by_minutes', 'tariffication_full_first_minute', 'tariffication_free_first_seconds',
                ],
                'integer'
            ],
            [['month_line','month_number','once_line','once_number','month_min_payment'], 'number'],
            [['currency_id','name','name_short','status',], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'country_id' => 'Страна',
            'connection_point_id' => 'Точка подключения',
            'currency_id' => 'Валюта',
            'dest' => 'Направление',
            'status' => 'Состояние',
            'pricelist_id' => 'Прайс-лист',
            'name' => 'Название',
            'name_short' => 'Краткое название',
            'month_line' => 'ежемесячная плата за линию',
            'month_number' => 'ежемесячная плата за номер',
            'month_min_payment' => 'минимальный платеж 7800',
            'once_line' => 'плата за подключение линии',
            'once_number' => 'плата за подключение номера',
            'free_local_min' => 'бесплатных местных минут',
            'freemin_for_number' => 'бесплатные минуты для номера (да) или для линии (нет)',
            'paid_redirect' => 'платные переадресации',
            'tariffication_by_minutes' => 'тарификация: поминутная (да), посекундная (нет)',
            'tariffication_full_first_minute' => 'тарификация: первая минута оплачивается полностью',
            'tariffication_free_first_seconds' => 'тарификация: первые 5 секунд бесплатно',
            'is_virtual' => 'тариф для виртуальных номеров',
            'is_testing' => 'тариф по-умолчанию',
            'price_include_vat' => 'включить в цену ставку налога',
        ];
    }

    public function save(TariffVoip $tariff = null)
    {
        if ($tariff  === null) {
            $tariff = new TariffVoip;
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