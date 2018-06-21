<?php

namespace app\modules\uu\models;

/**
 * Универсальная услуга для телефонии
 */
class AccountTariffVoip extends AccountTariff
{
    public $voip_country_id = null;
    public $voip_ndc_type_id = null;
    public $voip_did_group = null;
    public $operator_account_id = null;
    public $voip_numbers_list_class = 3;
    public $voip_numbers_list_order_by_field = 'number';
    public $voip_numbers_list_order_by_type = SORT_ASC;
    public $voip_numbers_list_mask = '';
    public $voip_numbers_list_limit = 40;
    public $voip_numbers = []; // Номера
    public $voip_numbers_warehouse_status = null; // Синхронизированный статус склада сим-карты

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return (parent::attributeLabels() + [
                'voip_country_id' => 'Страна',
                'voip_ndc_type_id' => 'Тип NDC',
                'voip_did_group' => 'DID-группа',
                'operator_account_id' => 'Оператор',
                'voip_numbers' => 'Номера',
                'voip_numbers_list_class' => 'Количество столбцов',
                'voip_numbers_list_order_by_field' => 'Сортировка по полю',
                'voip_numbers_list_order_by_type' => 'Тип сортировки',
                'voip_numbers_list_mask' => 'Шаблон поиска',
                'voip_numbers_list_limit' => 'Количество на странице',
                'voip_numbers_warehouse_status' => 'Статус склада мобильных номеров',
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
                    'operator_account_id',
                    'voip_numbers_list_class',
                    'voip_numbers_list_order_by_type',
                    'voip_numbers_list_limit',
                    'city_id',
                    'voip_numbers_warehouse_status',
                ],
                'integer'
            ],
            [['voip_ndc_type_id', 'voip_numbers_list_order_by_field', 'voip_numbers_list_mask'], 'string'],
            ['voip_numbers', 'each', 'rule' => ['match', 'pattern' => '/^\d{4,15}$/']],
            [['voip_numbers'], 'required'],
        ];
    }
}