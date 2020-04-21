<?php

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ResourceClass;

// начало действиях всех ресурсов должно совпадать с uu_account_tariff_log!
$dateTimeCurrent = (new DateTimeImmutable())
    ->setTime(0, 0, 0);

$dateTimeFirstTomorrow = $dateTimeCurrent
    ->modify('+1 day');

$dateTimeFirstDayOfPrevMonth = $dateTimeCurrent
    ->modify('first day of previous month');

$dateTimeYesterday = (new DateTimeImmutable())
    ->modify('-1 day')
    ->setTime(0, 0, 0);

$accountTariffResourceLogs = [
    // тест 2
    [
        // 1го сразу же подключил дневной тариф
        // 2го сразу же подключил месячный тариф
        // 4го сразу же подключил годовой тариф
        //
        // 1го с 1го увеличил до 3х линий
        // 1го с 1го увеличил линии до 6х
        // 1го с 3го уменьшил линии до 2х (до смены тарифа не должно учитываться, потом - должно)
        // с завтра увеличил до 10 линий (не должно учитываться)

        // по дневному тарифу:
        //      1-1: 1 линия (бесплатно)
        //      1-1: +2 линий
        //      1-1: +3 линий
        // по месячному тарифу:
        //      2-30: 1 линия (бесплатно)
        //      2-30: +5 линий
        // по годовому тарифу:
        //      4-30: 1 линия (бесплатно)
        //      4-30: +1 линии и еще 11 месяцев 1-30 числа
        //
        // всего должно быть 3 + 12 = 15 платных транзакций

        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_ABONENT,
        'amount' => 3,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_ABONENT,
        'amount' => 6,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_ABONENT,
        'amount' => 2,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_ABONENT,
        'amount' => 10,
        'actual_from_utc' => $dateTimeFirstTomorrow->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstTomorrow->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_EXT_DID,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_RECORD,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_FAX,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_MIN_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_GEO_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_SUB_ACCOUNT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_VOICE_ASSISTANT,
        'amount' => 0,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => ResourceClass::ID_VPBX_ROBOT_CONTROLLER,
        'amount' => 0,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
];

$resources = [
    ResourceClass::ID_VPBX_ABONENT,
    ResourceClass::ID_VPBX_EXT_DID,
    ResourceClass::ID_VPBX_RECORD,
    ResourceClass::ID_VPBX_FAX,
    ResourceClass::ID_VPBX_MIN_ROUTE,
    ResourceClass::ID_VPBX_GEO_ROUTE,
    ResourceClass::ID_VPBX_SUB_ACCOUNT,
    ResourceClass::ID_VPBX_VOICE_ASSISTANT,
    ResourceClass::ID_VPBX_ROBOT_CONTROLLER

];
$accountTariffIds = [
    AccountTariff::DELTA + 1,
    AccountTariff::DELTA + 3,
    AccountTariff::DELTA + 4,
];

foreach ($accountTariffIds as $accountTariffId) {
    foreach ($resources as $resource) {
        $accountTariffResourceLogs[] = [
            // инициализация с 1го
            'account_tariff_id' => $accountTariffId,
            'resource_id' => $resource,
            'amount' => 1,
            'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
            'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        ];
    }
}

return $accountTariffResourceLogs;