<?php

return [

    'voip_group_local' => 'местные мобильные',
    'voip_group_long_distance' => 'междугородные',
    'voip_group_international' => 'дальнее зарубежье',

    'voip_connection' => 'Подключение к IP-телефонии по тарифу {tariff}',

    // Client "bill_rename1" = Абонентская плата по
    'voip_monthly_fee_per_number' => 'Абонентская плата за телефонный номер {service}{date_range}',

    // Client "bill_rename1" = Оказанные услуги по Договору
    'voip_monthly_fee_per_number_custom' => 'Оказанные услуги за телефонный номер {service}{date_range}{by_agreement}',

    // Client "bill_rename1" = Абонентская плата по
    'voip_monthly_fee_per_line' => 'Абонентская плата за {lines_number} телефонн{plural_first} лин{plural_second} к номеру {service}',
    // Yii::t plural not work
    // https://github.com/yiisoft/yii2/issues/4259

    // Client "bill_rename1" = Оказанные услуги по Договору
    'voip_monthly_fee_per_line_custom' => 'Оказанные услуги за {lines_number} телефонн{plural_first} лин{plural_second} к номеру {service}{by_agreement}',
    // Yii::t plural not work
    // https://github.com/yiisoft/yii2/issues/4259

    'voip_overlimit' => 'Превышение лимита, включенного в абонентскую плату по номеру {service} (местные вызовы){date_range}',

    'voip_local_mobile_call_minpay' => 'Минимальный платеж за звонки на местные мобильные с номера {service}{date_range}',
    'voip_local_mobile_call_payment' => 'Плата за звонки на местные мобильные с номера {service}{date_range}',

    'voip_long_distance_call_minpay' => 'Минимальный платеж за междугородные звонки с номера {service}{date_range}',
    'voip_long_distance_call_payment' => 'Плата за междугородные звонки с номера {service}{date_range}',

    'voip_international_call_minpay' => 'Минимальный платеж за международные звонки с номера {service}{date_range}',
    'voip_international_call_payment' => 'Плата за международные звонки с номера {service}{date_range}',

    'voip_group_minpay' => 'Минимальный платеж за набор ({group}) с номера {service}{date_range}',
    'voip_group_payment' => 'Плата за звонки в наборе ({group}) с номера {service}{date_range}',

    'voip_calls_minpay' => 'Минимальный платеж за звонки по номеру {service}{date_range}',
    // Client "bill_rename1" = Абонентская плата по
    'voip_calls_payment' => 'Плата за звонки по номеру {service}{date_range}',
    // Client "bill_rename1" = Оказанные услуги по Договору
    'voip_calls_payment_custom' => 'Оказанные услуги за звонки по номеру {service}{date_range}{by_agreement}',

    // Client "bill_rename1" = Абонентская плата по
    'voip_group_calls_payment' => 'Плата за звонки по номеру {service} (местные, междугородные, международные){date_range}',
    // Client "bill_rename1" = Оказанные услуги по Договору
    'voip_group_calls_payment_custom' => 'Оказанные услуги за звонки по номеру {service} (местные, междугородные, международные){date_range}{by_agreement}',

    'voip_package_monfly_fee' => 'Абонентская плата за пакет минут {service}{date_range}',

    'voip_sip_trunk_monfly_fee' => 'Абонентская плата за Sip транк по тарифу {tariff}{date_range}',

    'voip_package_fee' => 'Абонентская плата за пакет "{tariff}" к номеру {service}{date_range}',
    'voip_package_payment' => 'Абонентская плата за пакет "{tariff}" к номеру {service}{date_range}',
    'voip_package_minpay' => 'Минимальный платеж за пакет "{tariff}" к номеру {service}{date_range}',

    'voip_operator_trunk_plus' => 'Платный траффик со стороны оператора по транку {service}{date_range}',
    'voip_operator_trunk_minus' => 'Платный траффик на оператора по транку {service}{date_range}',

];
