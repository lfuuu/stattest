<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\TariffVirtpbx;
use app\models\ClientAccount;

class VirtpbxServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        return
            UsageVirtpbx::find()
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

    public function getTariffsList(ClientAccount $client)
    {
        return
            TariffVirtpbx::find()
                ->where(['currency' => $client->currency])
                ->andWhere(['!=', 'status', 'archive'])
                ->all();
    }

}
