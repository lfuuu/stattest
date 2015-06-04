<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\UsageTrunk;
use app\models\ClientAccount;

class TrunkServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageTrunk::find()
                ->andWhere(['client_account_id' => $client->id])
                ->andWhere('actual_from <= :date', [':date' => $now->format('Y-m-d')])
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}