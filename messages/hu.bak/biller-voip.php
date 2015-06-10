<?php

return [

    'group_local' => 'местные мобильные',
    'group_long_distance' => 'междугородные',
    'group_too_far_countries' => 'дальнее зарубежье',
    'group_countries' => 'ближнее зарубежье',

    'connection' => 'Подключение к IP-телефонии по тарифу {tariff}',

    // Client "bill_rename1" = Абонентская плата по
    'connection_payment' => 'Абонентская плата за телефонный номер {service} {date_range}',

    // Client "bill_rename1" = Оказанные услуги по Договору
    'connection_payment_custom' => 'Оказанные услуги за телефонный номер {service} {date_range}, согласно Договора {contract_no} от {contract_date, date,dd MMM YYYY}',

    // Client "bill_rename1" = Абонентская плата по
    'connection_payment_extend' => 'Абонентская плата за {lines_number} телефонн{plural_first} лин{plural_second} к номеру {service}',
    // Yii::t plural not work
    // https://github.com/yiisoft/yii2/issues/4259

    // Client "bill_rename1" = Оказанные услуги по Договору
    'connection_payment_extend_custom' => 'Оказанные услуги за {lines_number} телефонн{plural_first} лин{plural_second} к номеру {service}, согласно Договора {contract_no} от {contract_date, date,dd MMM YYYY}',
    // Yii::t plural not work
    // https://github.com/yiisoft/yii2/issues/4259

    'overlimit' => 'Превышение лимита, включенного в абонентскую плату по номеру {number} (местные вызовы) {period}',

    'minimal_payment_local_numbers' => 'Минимальный платеж за звонки на местные мобильные с номера {number} {period}',

    'payment_local_numbers' => 'Плата за звонки на местные мобильные с номера {number} {period}',

    'minimal_payment_long_distance_calls' => 'Минимальный платеж за междугородные звонки с номера {number} {period}',

    'payment_long_distance_calls' => 'Плата за междугородные звонки с номера {number} {period}',

    'minimal_payment_too_far_countries' => 'Минимальный платеж за звонки в дальнее зарубежье с номера {number} {period}',

    'payment_too_far_countries' => 'Плата за звонки в дальнее зарубежье с номера {number} {period}',

    'minimal_payment_countries' => 'Минимальный платеж за звонки в ближнее зарубежье с номера {number} {period}',

    'payment_countries' => 'Плата за звонки в ближнее зарубежье с номера {number} {period}',

    'minimal_payment_for_group' => 'Минимальный платеж за набор ({group}) с номера {number} {period}',

    'payment_for_group' => 'Плата за звонки в наборе ({group}) с номера {number} {period}',

    'minimal_payment' => 'Минимальный платеж за звонки по номеру {number} {period}',

    // Client "bill_rename1" = Абонентская плата по
    'payment' => 'Плата за звонки по номеру {number} {period}',

    // Client "bill_rename1" = Оказанные услуги по Договору
    'payment_custom' => 'Оказанные услуги за звонки по номеру {number} {period}, согласно Договора {contract_no} от {contract_date, date,dd MMM YYYY}',

    // Client "bill_rename1" = Абонентская плата по
    'payment_complex' => 'Плата за звонки по номеру {number} (местные, междугородные, международные) {period}',

    // Client "bill_rename1" = Оказанные услуги по Договору
    'payment_complex_custom' => 'Оказанные услуги за звонки по номеру {number} (местные, междугородные, международные) {period}, согласно Договора {contract_no} от {contract_date, date,dd MMM YYYY}'

];