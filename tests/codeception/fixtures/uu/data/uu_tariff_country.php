<?php

use app\models\Country;
use app\modules\uu\models\Tariff;

$tariffIds = [
    Tariff::DELTA + 1,
    Tariff::DELTA + 2,
    Tariff::DELTA + 3,
    Tariff::DELTA + 4,
    Tariff::DELTA + 13,
    Tariff::DELTA + 6,
    Tariff::DELTA + 7,
    Tariff::DELTA + 8,
    Tariff::DELTA + 9,
    Tariff::DELTA + 10,
    Tariff::DELTA + 11,
    Tariff::DELTA + 12,
    Tariff::TEST_VOIP_ID,
    Tariff::TEST_VPBX_ID,
    Tariff::START_VPBX_ID,
];
$tariffCountries = [];
foreach ($tariffIds as $tariffId) {
    $tariffCountries[] = [
        'id' => $tariffId,
        'tariff_id' => $tariffId,
        'country_id' => Country::RUSSIA,
    ];
}

return $tariffCountries;