<?php

use app\modules\uu\models\Resource;
use app\modules\uu\models\Tariff;

$vpbxBaseResources = [
    [
        'amount' => 0.2,
        'price_per_unit' => 98,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_DISK,
    ],
    [
        'amount' => 3,
        'price_per_unit' => 39,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_ABONENT,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_RECORD,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_FAX,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
    ],
];

$vpbxResources = [];

$v = 0;
foreach ([
             Tariff::DELTA + 3,
             Tariff::TEST_VPBX_ID,
             Tariff::START_VPBX_ID
         ] as $tariffId) {
    $vpbxResourcesTmp = $vpbxBaseResources;
    foreach ($vpbxResourcesTmp as &$resource) {
        $resource['tariff_id'] = $tariffId;

        $v++;
    }
    $vpbxResources = array_merge($vpbxResources, $vpbxResourcesTmp);
}


$allTariffResources = [
    // тариф 1
    [
        'amount' => 0.04,
        'price_per_unit' => 118,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_DISK,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 1,
        'price_per_unit' => 59,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_FAX,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'tariff_id' => Tariff::DELTA + 1,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'tariff_id' => Tariff::DELTA + 1,
    ],


    // тариф 2
    [
        'amount' => 0.1,
        'price_per_unit' => 108,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_DISK,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 2,
        'price_per_unit' => 49,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_FAX,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'tariff_id' => Tariff::DELTA + 2,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'tariff_id' => Tariff::DELTA + 2,
    ],

    // Тариф 4
    [
        'amount' => 1,
        'price_per_unit' => 98,
        'price_min' => 0,
        'resource_id' => Resource::ID_VOIP_LINE,
        'tariff_id' => Tariff::DELTA + 4,
    ],
    [
        'amount' => 1,
        'price_per_unit' => 98,
        'price_min' => 0,
        'resource_id' => Resource::ID_VOIP_FMC,
        'tariff_id' => Tariff::DELTA + 4,
    ],
    [
        'amount' => 1,
        'price_per_unit' => 99,
        'price_min' => 0,
        'resource_id' => Resource::ID_VOIP_MOBILE_OUTBOUND,
        'tariff_id' => Tariff::DELTA + 4,
    ],

    // Тариф 6
    [
        'amount' => 0.2,
        'price_per_unit' => 98,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_DISK,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 3,
        'price_per_unit' => 39,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_ABONENT,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_EXT_DID,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_RECORD,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_FAX,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_MIN_ROUTE,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_GEO_ROUTE,
        'tariff_id' => Tariff::DELTA + 6,
    ],
    [
        'amount' => 0,
        'price_per_unit' => 9,
        'price_min' => 0,
        'resource_id' => Resource::ID_VPBX_SUB_ACCOUNT,
        'tariff_id' => Tariff::DELTA + 6,
    ],

    // Тариф 10
    [
        'amount' => 1,
        'price_per_unit' => 39,
        'price_min' => 0,
        'resource_id' => Resource::ID_VOIP_PACKAGE_SMS,
        'tariff_id' => Tariff::DELTA + 10,
    ],
];

return array_merge($allTariffResources, $vpbxResources);