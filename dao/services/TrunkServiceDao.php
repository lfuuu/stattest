<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\UsageTrunk;
use app\models\ClientAccount;

class TrunkServiceDao extends Singleton
{

    /**
     * @param ClientAccount $client
     * @return bool
     */
    public function hasService(ClientAccount $client)
    {
        return UsageTrunk::find()->where(['client_account_id' => $client->id])->count() > 0;
    }
}