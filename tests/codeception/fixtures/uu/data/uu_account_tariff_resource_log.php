<?php

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;

// начало действиях всех ресурсов должно совпадать с uu_account_tariff_log!
//
$dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())
    ->modify('first day of previous month')
    ->setTime(0, 0, 0);

return [

    // Tariff with autoprolongation
    // тест 1
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
        // 1го и 2го числа - 1 линия
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 2го с 3го подключил месячный тариф
        // 3го с 5го увеличил линии до 3х
        // 3-4го - 1 линия, с 5го до конца прошлого месяца - 3 линии
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 3,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+4 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+2 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 6го с 7го уменьшил линии до 2х
        // весь этот месяц - 2 линии
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 2,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+4 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+2 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // тест 2
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
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

    // Tariff without autoprolongation
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_FAX,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // инициализация с 1го
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'amount' => 1,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
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
];