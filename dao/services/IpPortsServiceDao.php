<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageIpPorts;

class IpPortsServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageIpPorts::find()
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}
