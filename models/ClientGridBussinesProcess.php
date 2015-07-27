<?php
namespace app\models;

use yii\db\ActiveRecord;
class ClientGridBussinesProcess extends ActiveRecord
{

    // Телеком
    const TELECOM_MAINTENANCE = 1; // Сопровождение
    const TELECOM_SALES = 2; // Продажи

    // Интернет магазин
    const INTERNET_SHOP_MAINTENANCE = 4; // Сопровождение

    // Поставщик
    const PROVIDER_ORDERS = 5; // Заказы
    const PROVIDER_MAINTENANCE = 6; // Сопровождение

    // Партнер
    const PARTNER_MAINTENANCE = 8; // Сопровождение

    // Внутренний офис
    const INTERNAL_OFFICE = 10; // Внутренний офис

    // Оператор
    const OPERATOR_OPERATORS = 11; // Операторы
    const OPERATOR_CLIENTS = 12; // Клиенты
    const OPERATOR_INFRASTRUCTURE = 13; // Инфраструктура
    const OPERATOR_FORMAL = 14; // Формальные

    // Welltime
    const WELLTIME_MAINTENANCE = 15; // Сопровождение

    public static function tableName()
    {
        return 'grid_business_process';
    }
}
