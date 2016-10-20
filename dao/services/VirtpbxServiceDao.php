<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\TariffVirtpbx;
use app\models\ClientAccount;
use app\models\UsageVirtpbx;

class VirtpbxServiceDao extends Singleton
{

    public function getTariffsList(ClientAccount $client)
    {
        return
            TariffVirtpbx::find()
                ->where(['currency' => $client->currency])
                ->andWhere(['!=', 'status', 'archive'])
                ->all();
    }

}
