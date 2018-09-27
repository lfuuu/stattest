<?php

use app\modules\uu\models\AccountTariffVoipFlat;

/**
 * Class m180628_074947_uu_account_tariff_voip_flat
 */
class m180628_074947_account_tariff_voip_flat extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(AccountTariffVoipFlat::tableName(), [
            'account_tariff_id' => $this->integer()->comment('ID услуги'),
            'tariff_period' => $this->string()->comment('У-услуга'),
            'tariff_is_include_vat' => $this->boolean()->comment('Включая НДС'),
            'tariff_is_postpaid' => $this->boolean()->comment('Постоплата'),
            'tariff_country' => $this->string()->comment('Страна'),
            'tariff_currency' => $this->string()->comment('Валюта'),
            'tariff_organization' => $this->string()->comment('Организации'),
            'tariff_is_default' => $this->boolean()->comment('По умолчанию'),
            'tariff_status' => $this->string()->comment('Статус тарифа'),
            'client_account' => $this->string()->comment('УЛС'),
            'region' => $this->string()->comment('Регион (точка подключения)'),
            'comment' => $this->string()->comment('Комментарий'),
            'tariff_period_utc' => $this->dateTime()->comment('Дата последней смены тарифа'),
            'account_log_period_utc' => $this->dateTime()->comment('Абонентка списана до'),
            'city' => $this->string()->comment('Город'),
            'voip_number' => $this->bigInteger(20)->comment('Номер'),
            'beauty_level' => $this->string()->comment('Красивость'),
            'ndc_type' => $this->string()->comment('Тип NDC'),
            'test_connect_date' => $this->dateTime()->comment('Дата включения на тестовый тариф'),
            'date_sale' => $this->dateTime()->comment('Дата продажи'),
            'date_before_sale' => $this->dateTime()->comment('Дата допродажи'),
            'disconnect_date' => $this->dateTime()->comment('Дата отключения'),
            'account_manager_name' => $this->string()->comment('Ак. менеджер')
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(AccountTariffVoipFlat::tableName());
    }
}