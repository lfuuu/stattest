<?php

use app\helpers\DateTimeZoneHelper;

$dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

return [

    // Tariff with autoprolongation
    // тест 1
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца
        'account_tariff_id' => 1,
        'tariff_period_id' => 1, // по дням
        'actual_from' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 2го с 3го подключил месячный тариф
        // по этому тарифу с 3го до конца прошлого месяца и весь этот месяц
        'account_tariff_id' => 1,
        'tariff_period_id' => 2, // по месяцам
        'actual_from' => $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // тест 2
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца
        'account_tariff_id' => 2,
        'tariff_period_id' => 1, // по дням
        'actual_from' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 2го сразу же подключил месячный тариф
        // по этому тарифу со 2го до конца прошлого месяца
        'account_tariff_id' => 2,
        'tariff_period_id' => 2, // по месяцам
        'actual_from' => $dateTimeFirstDayOfPrevMonth->modify('+1 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 4го сразу же подключил годовой тариф
        // по этому тарифу с 4го до конца этого года
        'account_tariff_id' => 2,
        'tariff_period_id' => 3, // по годам
        'actual_from' => $dateTimeFirstDayOfPrevMonth->modify('+3 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+3 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // Tariff without autoprolongation
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца, потому что должен закрыться автоматически на следующий день
        'account_tariff_id' => 3,
        'tariff_period_id' => 4,
        'actual_from' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца, потому что должен закрыться автоматически через день
        'account_tariff_id' => 4,
        'tariff_period_id' => 5,
        'actual_from' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
];