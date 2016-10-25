<?php

namespace app\classes\transfer;

use app\models\ClientAccount;
use app\models\UsageWelltime;

class WelltimeServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageWelltime[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageWelltime::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}