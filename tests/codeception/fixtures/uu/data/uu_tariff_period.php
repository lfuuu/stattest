<?php

use app\classes\uu\model\Period;

return [
    [
        'id' => 1,
        'price_per_period' => 111,
        'price_setup' => 112,
        'price_min' => 113,
        'tariff_id' => 1,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_DAY,
    ],
    [
        'id' => 2,
        'price_per_period' => 211,
        'price_setup' => 212,
        'price_min' => 213,
        'tariff_id' => 1,
        'period_id' => Period::ID_MONTH,
        'charge_period_id' => Period::ID_MONTH,
    ],
    [
        'id' => 3,
        'price_per_period' => 311,
        'price_setup' => 312,
        'price_min' => 313,
        'tariff_id' => 1,
        'period_id' => Period::ID_QUARTER,
        'charge_period_id' => Period::ID_YEAR,
    ],
];