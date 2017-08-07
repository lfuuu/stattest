<?php

use app\modules\uu\models\Period;
use app\modules\uu\models\Tariff;

return [
    // Tariff with autoprolongation
    [
        'id' => 1,
        'price_per_period' => 111,
        'price_setup' => 112,
        'price_min' => 113,
        'tariff_id' => Tariff::DELTA + 1,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 2,
        'price_per_period' => 211,
        'price_setup' => 212,
        'price_min' => 213,
        'tariff_id' => Tariff::DELTA + 1,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 3,
        'price_per_period' => 311,
        'price_setup' => 312,
        'price_min' => 313,
        'tariff_id' => Tariff::DELTA + 1,
        'charge_period_id' => Period::ID_YEAR,
    ],

    // Tariff without autoprolongation
    [
        'id' => 4,
        'price_per_period' => 411,
        'price_setup' => 412,
        'price_min' => 413,
        'tariff_id' => Tariff::DELTA + 2,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 5,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 3,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 6,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 4,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 7,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 5,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 8,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 6,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 9,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 7,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 10,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 8,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 11,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 9,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 12,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 10,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 13,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 11,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 14,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 12,
        'charge_period_id' => Period::ID_MONTH,
    ],
];