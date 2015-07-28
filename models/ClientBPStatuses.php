<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

class ClientBPStatuses
{
    // Общие
    const STATE_NEGOTIATIONS = 11; // Переговоры

    // Телеком - Сопровождение
    const TELEKOM_MAINTENANCE_ORDER_OF_SERVICES = 19; // Заказ услуг
    const TELEKOM_MAINTENANCE_CONNECTED = 8; //Подключаемые
    const TELEKOM_MAINTENANCE_WORK = 9; // Включенные
    const TELEKOM_MAINTENANCE_DISCONNECTED = 10; // Отключенные
    const TELEKOM_MAINTENANCE_DISCONNECTED_DEBT = 11; // Отключенные за долги
    const TELEKOM_MAINTENANCE_TRASH = 22; // Мусор
    const TELEKOM_MAINTENANCE_NOT_CONNECTED = 0; // Не привязанные
    const TELEKOM_MAINTENANCE_TECH_FAILURE = 27; // Тех. отказ
    const TELEKOM_MAINTENANCE_FAILURE = 28; // Отказ
    const TELEKOM_MAINTENANCE_DUPLICATE = 29; // Дубликат

    // Поставщик
    const PROVIDER_ORDERS_ACTING = 32; // Заказы - Действущий
    const PROVIDER_ORDERS_NEGOTIATION_STAGE = 36; // Заказы - В стадии переговоров
    const PROVIDER_MAINTENANCE_GPON = 108; // Сопровождение - GPON
    const PROVIDER_MAINTENANCE_VOLS = 109; // Сопровождение - ВОЛС
    const PROVIDER_MAINTENANCE_SERVICE = 110; // Сопровождение - Сервисный
    const PROVIDER_MAINTENANCE_ACTING = 15; // Сопровождение - Действущий
    const PROVIDER_MAINTENANCE_CLOSED = 92; // Сопровождение - Закрытый
    const PROVIDER_MAINTENANCE_SELF_BUY = 93; // Сопровождение - Самозакупки
    const PROVIDER_MAINTENANCE_ONCE = 94; // Сопровождение - Разовый

    // Партнер
    const PARTNER_MAINTENANCE_NEGOTIATIONS = 24; // Сопровождение - Переговоры
    const PARTNER_MAINTENANCE_ACTING = 35; // Сопровождение - Действующий
    const PARTNER_MAINTENANCE_CLOSED = 26; // Сопровождение - Закрытый

    // Внутренний офис
    const INTERNAL_OFFICE = 34; // Внутренний офис
    const INTERNAL_OFFICE_CLOSED = 111; // Закрытые

    // Оператор
    const OPERATOR_OPERATORS_INCOMMING = 37; // Операторы - Входящий
    const OPERATOR_OPERATORS_NEGOTIATIONS = 38; // Операторы - Переговоры
    const OPERATOR_OPERATORS_TESTING = 39; // Операторы - Тестирование
    const OPERATOR_OPERATORS_ACTING = 40; // Операторы - Действующий
    const OPERATOR_OPERATORS_MANUAL_BILL = 107; // Операторы - Ручной счет
    const OPERATOR_OPERATORS_SUSPENDED = 41; // Операторы - Приостановлен
    const OPERATOR_OPERATORS_TERMINATED = 42; // Операторы - Расторгнут
    const OPERATOR_OPERATORS_BLOCKED = 43; // Операторы - Фрод блокировка
    const OPERATOR_OPERATORS_TECH_FAILURE = 44; // Операторы - Техотказ
    const OPERATOR_OPERATORS_AUTO_BLOCKED = 45; // Операторы - Автоблокировка
    const OPERATOR_CLIENTS_INCOMMING = 47; // Клиенты - Входящий
    const OPERATOR_CLIENTS_NEGOTIATIONS = 48; // Клиенты - Переговоры
    const OPERATOR_CLIENTS_TESTING = 49; // Клиенты - Тестирование
    const OPERATOR_CLIENTS_ACTING = 50; // Клиенты - Действующий
    const OPERATOR_CLIENTS_SUSPENDED = 51; // Клиенты - Приостановлен
    const OPERATOR_CLIENTS_TERMINATED = 52; // Клиенты - Расторгнут
    const OPERATOR_CLIENTS_BLOCKED = 53; // Клиенты - Фрод блокировка
    const OPERATOR_CLIENTS_TECH_FAILURE = 54; // Клиенты - Техотказ
    const OPERATOR_INFRASTRUCTURE_INCOMMING = 62; // Инфраструктура - Входящий
    const OPERATOR_INFRASTRUCTURE_NEGOTIATIONS = 63; // Инфраструктура - Переговоры
    const OPERATOR_INFRASTRUCTURE_TESTING = 64; // Инфраструктура - Тестирование
    const OPERATOR_INFRASTRUCTURE_ACTING = 65; // Инфраструктура - Действующий
    const OPERATOR_INFRASTRUCTURE_SUSPENDED = 66; // Инфраструктура - Приостановлен
    const OPERATOR_INFRASTRUCTURE_TERMINATED = 67; // Инфраструктура - Расторгнут
    const OPERATOR_INFRASTRUCTURE_BLOCKED = 68; // Инфраструктура - Фрод блокировка
    const OPERATOR_INFRASTRUCTURE_TECH_FAILURE = 69; // Инфраструктура - Техотказ
    const OPERATOR_FORMAL_INCOMMING = 77; // Формальные - Входящий
    const OPERATOR_FORMAL_NEGOTIATIONS = 78; // Формальные - Переговоры
    const OPERATOR_FORMAL_TESTING = 79; // Формальные - Тестирование
    const OPERATOR_FORMAL_ACTING = 80; // Формальные - Действующий
    const OPERATOR_FORMAL_SUSPENDED = 81; // Формальные - Приостановлен
    const OPERATOR_FORMAL_TERMINATED = 82; // Формальные - Расторгнут
    const OPERATOR_FORMAL_BLOCKED = 83; // Формальные - Фрод блокировка
    const OPERATOR_FORMAL_TECH_FAILURE = 84; // Формальные - Техотказ


    const FOLDER_TELECOM_AUTOBLOCK = 21;
}
