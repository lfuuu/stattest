<?php

use app\modules\uu\models\AccountTariff;
use app\helpers\DateTimeZoneHelper;

$dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())
    ->modify('first day of previous month')
    ->setTime(0, 0, 0);

$dateTimeYesterday = (new DateTimeImmutable())
    ->modify('-1 day')
    ->setTime(0, 0, 0);

return [

    // Tariff with autoprolongation
    // тест 1
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'tariff_period_id' => 1, // по дням
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 2го с 3го подключил месячный тариф
        // по этому тарифу с 3го до конца прошлого месяца и весь этот месяц
        'account_tariff_id' => AccountTariff::DELTA + 1,
        'tariff_period_id' => 2, // по месяцам
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // тест 2
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'tariff_period_id' => 1, // по дням
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 2го сразу же подключил месячный тариф
        // по этому тарифу со 2го до конца прошлого месяца
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'tariff_period_id' => 2, // по месяцам
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+1 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 4го сразу же подключил годовой тариф
        // по этому тарифу с 4го до конца этого месяца + еще 11 месяцев
        'account_tariff_id' => AccountTariff::DELTA + 2,
        'tariff_period_id' => 3, // по годам
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+3 days')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->modify('+3 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // Tariff without autoprolongation
    [
        // вчера подключил дневной тариф
        // по этому тарифу только вчера, потому что должен закрыться автоматически сегодня
        'account_tariff_id' => AccountTariff::DELTA + 3,
        'tariff_period_id' => 4,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // вчера подключил дневной тариф
        // по этому тарифу только вчера и сегодня, потому что должен закрыться автоматически завтра
        'account_tariff_id' => AccountTariff::DELTA + 4,
        'tariff_period_id' => 5,
        'actual_from_utc' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeYesterday->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],

    // ресурсы при пересекающихся сменах тарифов
    [
        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'tariff_period_id' => 1, // по дням
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 1го сразу же подключил месячный тариф
        // по этому тарифу абонентка с 1го до конца прошлого месяца и весь этот месяц, а ресурсы только за 1ое (и только 1 раз!)
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'tariff_period_id' => 2, // по месяцам
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
    [
        // 1го со 3го выключил
        'account_tariff_id' => AccountTariff::DELTA + 5,
        'tariff_period_id' => null,
        'actual_from_utc' => $dateTimeFirstDayOfPrevMonth->modify('+2 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        'insert_time' => $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATETIME_FORMAT),
    ],
];