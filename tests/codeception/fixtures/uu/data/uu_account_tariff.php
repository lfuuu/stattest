<?php

use app\classes\uu\model\ServiceType;
use tests\codeception\unit\models\_ClientAccount;

$account = _ClientAccount::createOne();

return [
    [
        'id' => 1,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
    ],
    [
        'id' => 2,
        'client_account_id' => $account->id,
        'service_type_id' => ServiceType::ID_VPBX,
    ],
];