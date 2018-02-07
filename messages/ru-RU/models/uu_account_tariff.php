<?php
return [
    'id' => 'ID',
    'client_account_id' => 'УЛС',
    'service_type_id' => 'Тип услуги',
    'region_id' => 'Точка присоединения',
    'city_id' => 'Город',
    'prev_account_tariff_id' => 'Основная услуга', // если из пакета, то ссылка на основную услугу "телефония"
    'comment' => 'Комментарий',
    'voip_number' => 'Номер',
    'vm_elid_id' => 'ID VM collocation',
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

    'insert_time' => 'Когда создал, UTC',
    'insert_user_id' => 'Кто создал',
    'update_time' => 'Когда редактировал, UTC',
    'update_user_id' => 'Кто редактировал',
];