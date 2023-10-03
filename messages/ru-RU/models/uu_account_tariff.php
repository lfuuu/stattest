<?php
return [
    'id' => 'ID',
    'client_account_id' => 'УЛС',
    'service_type_id' => 'Тип услуги',
    'region_id' => 'Регион (точка подключения)',
    'city_id' => 'Город',
    'prev_account_tariff_id' => 'Основная услуга', // если из пакета, то ссылка на основную услугу "телефония"
    'comment' => 'Технические данные (комментарий)',
    'voip_number' => 'Номер',
    'vm_elid_id' => 'ID VPS',
    'route_name' => 'Route Name',
    'trunk_id' => 'Транк',
    'tariff_period_id' => 'Тариф / период',
    'prev_usage_id' => 'Перенесено из',
    'next_usage_id' => 'Перенесено в', // это псевдо-поле
    'is_unzipped' => 'Разархивировано',
    'mtt_number' => 'Номер МТТ',
    'mtt_balance' => 'Баланс МТТ',
    'trunk_type_id' => 'Мега/мульти транк',
    'infrastructure_project' => 'Проект', // Инфраструктура
    'infrastructure_level' => 'Уровень', // Инфраструктура
    'price' => 'Цена (плюс - доход, минус - расход)', // Инфраструктура
    'datacenter_id' => 'Тех. площадка', // Инфраструктура
    'device_address' => 'Адрес установки оборудования', // Телефония
    'tariff_period_utc' => 'Дата последней смены тарифа',
    'account_log_period_utc' => 'Абонентка списана до',
    'account_log_resource_utc' => 'Ресурсы рассчитаны до',
    'calltracking_params' => 'Параметры Сalltracking',
    'iccid' => 'ICCID',

    'insert_time' => 'Когда создал',
    'insert_user_id' => 'Кто создал',
    'update_time' => 'Когда редактировал',
    'update_user_id' => 'Кто редактировал',
];