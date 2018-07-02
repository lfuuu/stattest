<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;

/**
 * @package app\modules\uu\models
 *
 * @property integer account_tariff_id
 * @property string tariff_period
 * @property boolean tariff_is_include_vat
 * @property boolean tariff_is_postpaid
 * @property string tariff_country
 * @property string tariff_currency
 * @property string tariff_organization
 * @property boolean tariff_is_default
 * @property string tariff_status
 * @property string client_account
 * @property string region
 * @property string comment
 * @property string tariff_period_utc
 * @property string account_log_period_utc
 * @property string city
 * @property integer voip_number
 * @property string beauty_level
 * @property string ndc_type
 * @property string test_connect_date
 * @property string date_sale
 * @property string date_before_sale
 * @property string disconnect_date
 * @property string account_manager_name
 */
class AccountTariffVoipFlat extends ActiveRecord
{
    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return ([
            'account_tariff_id' => 'ID услуги',
            'tariff_period' => 'У-услуги',
            'tariff_is_include_vat' => 'Включая НДС',
            'tariff_is_postpaid' => 'Постоплата',
            'tariff_country' => 'Страна',
            'tariff_currency' => 'Валюта',
            'tariff_organization' => 'Организации',
            'tariff_is_default' => 'По умолчанию',
            'tariff_status' => 'Статус тарифа',
            'client_account' => 'УЛС',
            'region' => 'Точка присоединения',
            'comment' => 'Комментарий',
            'tariff_period_utc' => 'Дата последней смены тарифа',
            'account_log_period_utc' => 'Абонентка списана до',
            'city' => 'Город',
            'voip_number' => 'Номер',
            'beauty_level' => 'Красивость',
            'ndc_type' => 'Тип NDC',
            'test_connect_date' => 'Дата включения на тестовый тариф',
            'date_sale' => 'Дата продажи',
            'date_before_sale' => 'Дата допродажи',
            'disconnect_date' => 'Дата отключения',
            'account_manager_name' => 'Ак. менеджер',
        ]);
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_voip_flat';
    }
}