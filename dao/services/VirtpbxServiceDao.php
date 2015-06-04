<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\UsageVirtpbx;
use app\models\ClientAccount;

class VirtpbxServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageVirtpbx::find()
                ->andWhere(['client' => $client->client])
                ->andWhere('actual_from <= :date', [':date' => $now->format('Y-m-d')])
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}