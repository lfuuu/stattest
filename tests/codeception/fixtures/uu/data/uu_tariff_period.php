<?php

use app\classes\uu\model\Period;
use app\classes\uu\model\Tariff;

return [
    // Tariff with autoprolongation
    [
        'id' => 1,
        'price_per_period' => 111,
        'price_setup' => 112,
        'price_min' => 113,
        'tariff_id' => Tariff::DELTA + 1,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 2,
        'price_per_period' => 211,
        'price_setup' => 212,
        'price_min' => 213,
        'tariff_id' => Tariff::DELTA + 1,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 3,
        'price_per_period' => 311,
        'price_setup' => 312,
        'price_min' => 313,
        'tariff_id' => Tariff::DELTA + 1,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_YEAR,
    ],

    // Tariff without autoprolongation
    [
        'id' => 4,
        'price_per_period' => 411,
        'price_setup' => 412,
        'price_min' => 413,
        'tariff_id' => Tariff::DELTA + 2,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 5,
        'price_per_period' => 511,
        'price_setup' => 512,
        'price_min' => 513,
        'tariff_id' => Tariff::DELTA + 3,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_DAY,
    ],
];