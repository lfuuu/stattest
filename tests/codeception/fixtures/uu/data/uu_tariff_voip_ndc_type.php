<?php

use app\models\City;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\Tariff;

return [
    [
        'tariff_id' => Tariff::DELTA + 4,
        'ndc_type_id' => NdcType::ID_GEOGRAPHIC,
    ],
    [
        'tariff_id' => Tariff::DELTA + 4,
        'ndc_type_id' => NdcType::ID_FREEPHONE,
    ],

    [
        'tariff_id' => Tariff::DELTA + 5,
        'ndc_type_id' => NdcType::ID_GEOGRAPHIC,
    ],
    [
        'tariff_id' => Tariff::DELTA + 5,
        'ndc_type_id' => NdcType::ID_FREEPHONE,
    ],

    [
        'tariff_id' => Tariff::DELTA + 13,
        'ndc_type_id' => NdcType::ID_GEOGRAPHIC,
    ],
    [
        'tariff_id' => Tariff::DELTA + 13,
        'ndc_type_id' => NdcType::ID_FREEPHONE,
    ],
];