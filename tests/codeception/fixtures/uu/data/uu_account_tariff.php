<?php

use app\models\Region;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\models\ClientAccount;
use tests\codeception\unit\models\_ClientAccount;

$account = _ClientAccount::createOne();
$account->account_version = ClientAccount::VERSION_BILLER_UNIVERSAL;
$account->save();

return [
    // Tariff with autoprolongation
    [
        'id' => AccountTariff::DELTA + 1,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
        'region_id' => Region::MOSCOW,
    ],
    [
        'id' => AccountTariff::DELTA + 2,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
        'region_id' => Region::MOSCOW,
    ],

    // Tariff without autoprolongation
    [
        'id' => AccountTariff::DELTA + 3,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
        'region_id' => Region::MOSCOW,
    ],
    [
        'id' => AccountTariff::DELTA + 4,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
        'region_id' => Region::MOSCOW,
    ],
    [
        'id' => AccountTariff::DELTA + 5,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
        'region_id' => Region::MOSCOW,
    ],

];