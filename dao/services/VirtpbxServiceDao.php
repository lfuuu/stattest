<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\TariffVirtpbx;
use app\models\ClientAccount;

class VirtpbxServiceDao extends Singleton
{

    /**
     * @param ClientAccount $clientAccount
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTariffsList(ClientAccount $clientAccount)
    {
        return
            TariffVirtpbx::find()
                ->where(['currency' => $clientAccount->currency])
                ->andWhere(['!=', 'status', 'archive'])
                ->all();
    }

}
