<?php

use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffStatus;
use app\models\Country;

return [
    [
        'id' => Tariff::DELTA + 1,
        'name' => 'Tariff with autoprolongation',
        'service_type_id' => ServiceType::ID_VPBX,
        'tariff_status_id' => TariffStatus::ID_PUBLIC,
        'country_id' => Country::RUSSIA,
        'tariff_person_id' => TariffPerson::ID_NATURAL_PERSON,
        'is_autoprolongation' => 1,
        'is_charge_after_blocking' => 0,
        'is_include_vat' => 1,
    ],
    [
        'id' => Tariff::DELTA + 2,
        'name' => 'Tariff without autoprolongation 0',
        'service_type_id' => ServiceType::ID_VPBX,
        'tariff_status_id' => TariffStatus::ID_PUBLIC,
        'country_id' => Country::RUSSIA,
        'tariff_person_id' => TariffPerson::ID_NATURAL_PERSON,
        'is_autoprolongation' => 0,
        'count_of_validity_period' => 0,
        'is_charge_after_blocking' => 0,
        'is_include_vat' => 1,
    ],
    [
        'id' => Tariff::DELTA + 3,
        'name' => 'Tariff without autoprolongation 1',
        'service_type_id' => ServiceType::ID_VPBX,
        'tariff_status_id' => TariffStatus::ID_PUBLIC,
        'country_id' => Country::RUSSIA,
        'tariff_person_id' => TariffPerson::ID_NATURAL_PERSON,
        'is_autoprolongation' => 0,
        'count_of_validity_period' => 1,
        'is_charge_after_blocking' => 0,
        'is_include_vat' => 1,
    ],
];