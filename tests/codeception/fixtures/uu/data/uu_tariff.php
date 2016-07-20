<?php

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\models\Country;

return [
    [
        'id' => 1,
        'name' => 'Any tariff',
        'service_type_id' => ServiceType::ID_VPBX,
        'tariff_status_id' => TariffStatus::ID_PUBLIC,
        'country_id' => Country::RUSSIA,
        'tariff_person_id' => TariffPerson::ID_NATURAL_PERSON,
    ],
];