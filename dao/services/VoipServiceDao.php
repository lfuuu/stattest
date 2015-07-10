<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\UsageVoip;
use app\models\ClientAccount;

class VoipServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageVoip::find()
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}
