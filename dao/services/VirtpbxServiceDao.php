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
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}
