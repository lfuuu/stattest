<?php

namespace app\classes\partners;

use app\models\ClientAccount;

interface RewardsInterface
{

    /**
     * @param int $usageId
     * @param int $accountVersion
     * @return mixed
     */
    public static function getUsage($usageId, $accountVersion = ClientAccount::VERSION_BILLER_USAGE);

}