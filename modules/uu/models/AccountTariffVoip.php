<?php

namespace app\modules\uu\models;

/**
 * Универсальная услуга для телефонии
 */
class AccountTariffVoip extends AccountTariff
{
    public $voip_country_id = null;
    public $voip_number_type = null;
    public $voip_did_group = null;
    public $voip_numbers_list_class = 2;
    public $voip_numbers_list_order_by_field = 'number';
    public $voip_numbers_list_order_by_type = SORT_ASC;
    public $voip_numbers_list_mask = '';
    public $voip_numbers_list_limit = 50;
    public $voip_numbers = []; // Номера

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return (parent::attributeLabels() + [
                'voip_country_id' => 'Страна',
                'voip_number_type' => 'Тип',
                'voip_did_group' => 'DID группа',
                'voip_numbers_list_class' => 'Количество столбцов',
                'voip_numbers_list_order_by_field' => 'Сортировка по полю',
                'voip_numbers_list_order_by_type' => 'Тип сортировки',
                'voip_numbers_list_mask' => 'Шаблон поиска',
                'voip_numbers_list_limit' => 'Количество на странице',
            ]);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'voip_country_id',
                    'voip_did_group',
                    'voip_numbers_list_class',
                    'voip_numbers_list_order_by_type',
                    'voip_numbers_list_limit',
                    'city_id'
                ],
                'integer'
            ],
            [['voip_number_type', 'voip_numbers_list_order_by_field', 'voip_numbers_list_mask'], 'string'],
            ['voip_numbers', 'each', 'rule' => ['match', 'pattern' => '/^\d{4,15}$/']],
            [['voip_numbers'], 'required'],
        ];
    }
}