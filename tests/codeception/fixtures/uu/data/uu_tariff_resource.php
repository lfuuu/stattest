<?php

use app\modules\uu\models\Tariff;

return [
    [
        'amount' => 0.04,
        'price_per_unit' => 118,
        'price_min' => 0,
        'resource_id' => 1, // Дисковое пространство
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 1,
        'price_per_unit' => 59,
        'price_min' => 0,
        'resource_id' => 2, // Абоненты
        'tariff_id' => Tariff::DELTA + 1,
    ],

    [
        'amount' => 0.1,
        'price_per_unit' => 108,
        'price_min' => 0,
        'resource_id' => 1, // Дисковое пространство
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 2,
        'price_per_unit' => 49,
        'price_min' => 0,
        'resource_id' => 2, // Абоненты
        'tariff_id' => Tariff::DELTA + 2,
    ],

    [
        'amount' => 0.2,
        'price_per_unit' => 98,
        'price_min' => 0,
        'resource_id' => 1, // Дисковое пространство
        'tariff_id' => Tariff::DELTA + 3,
    ],
    [
        'amount' => 3,
        'price_per_unit' => 39,
        'price_min' => 0,
        'resource_id' => 2, // Абоненты
        'tariff_id' => Tariff::DELTA + 3,
    ],

];