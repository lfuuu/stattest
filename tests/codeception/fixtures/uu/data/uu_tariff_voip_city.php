<?php

use app\models\City;
use app\modules\uu\models\Tariff;

return [
    [
        'tariff_id' => Tariff::DELTA + 4,
        'city_id' => City::DEFAULT_USER_CITY_ID,
    ],
    [
        'tariff_id' => Tariff::DELTA + 5,
        'city_id' => City::DEFAULT_USER_CITY_ID,
    ],
];