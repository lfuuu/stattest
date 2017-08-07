<?php

namespace app\modules\transfer\forms\services\decorators;

use app\models\ClientAccount;

class TrunkRegularServiceDecorator extends RegularServiceDecorator
{

    /**
     * @return string
     */
    public function getClientAccountUIDField()
    {
        return 'client_account_id';
    }

    /**
     * @param ClientAccount $clientAccount
     * @return int
     */
    public function getClientAccountUID(ClientAccount $clientAccount)
    {
        return $clientAccount->id;
    }


}