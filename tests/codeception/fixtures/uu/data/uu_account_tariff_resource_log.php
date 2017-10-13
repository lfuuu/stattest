<?php

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;

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

return [

    // Tariff with autoprolongation
    // тест 1
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_FAX,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // тест 2
    [
        // 1го сразу же подключил дневной тариф
        // 2го сразу же подключил месячный тариф
        // 4го сразу же подключил годовой тариф
        //
        // 3го с 3го увеличил до 3х линий
        // 6го с 7го увеличил линии до 6х
        // 6го с 8го уменьшил линии до 2х (не должно учитываться)
        // с завтра увеличил до 10 линий (не должно учитываться)

        // по дневному тарифу:
        //      1-1: 1 линия (бесплатно)
        //      2-2: 1 линия (бесплатно)
        // по месячному тарифу:
        //      2-30: 1 линия (бесплатно)
        //      3-30: +2 линии
        // по годовому тарифу:
        //      4-30: 1 линия (бесплатно)
        //      4-30: +2 линии и еще 11 месяцев 1-30 числа
        //      7-30: +3 линии и еще 11 месяцев 1-30 числа
        //
        // всего должно быть 1 + 2*12 = 25 платных транзакций
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 3,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+2 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 6,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+6 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+5 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 2,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+7 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+5 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 10,
        'actual_from_utc' => $dateTimeFirstTomorrow->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstTomorrow->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_FAX,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // Tariff without autoprolongation
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_FAX,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // ресурсы при пересекающихся сменах тарифов
    [
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_FAX,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
];