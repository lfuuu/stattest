<?php

namespace app\dao\services;

use app\models\ClientAccount;

interface ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client);

}